<?php

namespace App\Http\Controllers;

use App\BlockBooking;
use App\Booking;
use App\BookingRule;
use App\Club;
use App\Sport;
use App\StadiumFacility;
use App\Team;
use App\Tournament;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;
use App\Helpers\HtmlTemplatesHelper;

class BlockBookingController extends BaseController
{

    public function index()
    {
        if (request()->ajax()) {
            return datatables(BlockBooking::filters(request())
                ->with(['sport', 'tournament','bookings']))
                ->editColumn('is_payment_collected', static function ($data) {
                    $depositCount  = 0;
                    $fullPaymentCount = 0;
                    $uncollectedCount = 0;

                    foreach($data->bookings as $booking){
                        if($booking->slot_fee_deposit_paid){
                            $depositCount++;
                        }
                        if($booking->slot_fee_paid){
                            $fullPaymentCount++;
                        }else if($booking->slot_fee_paid === false && $booking->start_date_time < Carbon::now()){
                            $uncollectedCount++;
                        }
                    }
                    $payment[] = "<strong style='font-size: 0.8em'>Deposit Paid</strong>: {$depositCount}";
                    $payment[] = "<strong style='font-size: 0.8em'>P. Collected</strong>: {$fullPaymentCount}";
                    $payment[] = "<strong style='font-size: 0.8em'>Uncollected</strong>: {$uncollectedCount}";
                    return join("<br />", $payment);
                })->addColumn('actions', static function ($data) {
                    return HtmlTemplatesHelper::get_action_dropdown($data, '', false, false, auth()->user()->can('Bookings.Update'), auth()->user()->can('Bookings.Delete'),false,false);
                })
                ->addColumn('booking_count',function($data){
                    return count($data->bookings);
                })
                ->rawColumns(['actions','is_payment_collected'])->make(true);
        }
        return view('pages.block-booking.list', ['add_url' => route("block-booking.create"), 'breadcrumbs' => Breadcrumbs::generate('block-booking')]);
    }

    /**
     * @inheritDoc
     */
    public function create()
    {
        $users = User::where('status',1)->pluck('name', 'id');
        $sports = Sport::where('status', 1)->pluck('name', 'id');
        $clubs = Club::where('status',1)->pluck('name', 'id');
        $tournaments = Tournament::where('status', 1)->pluck('name', 'id');
        $location = [];
        $stadiumFacilityQuery = StadiumFacility::with(['sport'])->where('status', 1);
        if (!empty(auth()->user()->stadium_id)) {
            $stadiumFacilityQuery->whereHas('stadium', function ($query) {
                $query->where('id', auth()->user()->stadium_id);
            });
        }
        $stadiumFacility = $stadiumFacilityQuery->get();
        foreach ($stadiumFacility as $sf) {
            $location[$sf->sport_id][$sf->id] = $sf->name . ', ' . $sf->stadium->name;
        }


        return view('pages.block-booking.add', [
            'title' => 'Add Block Booking',
            'users' =>  $users,
            'sports' => $sports,
            'locations' => $location,
            'tournaments' => $tournaments,
            'clubs'=>$clubs
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, BlockBooking::$validation);

        $stadiumsByFacilityId = StadiumFacility::pluck('stadium_id', 'id');

        $blockBooking = new BlockBooking();
        $blockBooking->fill($request->all());

        DB::transaction(function () use ($blockBooking, $request, $stadiumsByFacilityId) {

            $blockBooking->save();
            $bookings = json_decode($request->bookings, true);
            foreach ($bookings as $booking) {
                $bModel = new Booking();
                $bModel->stadium_id = $stadiumsByFacilityId[$booking['location']];
                $bModel->stadium_facility_id = $booking['location'];
                $bModel->booking_date = $booking['bookingDate'];
                $bModel->tournament_id = $request->tournament_id;
                $bModel->contact_person_id = $blockBooking->contact_person_id;
                $bModel->start_time = $booking['startTime'];
                $bModel->end_time = $booking['endTime'];
                $bModel->block_booking_id = $blockBooking->id;

                $clubs = ['club1_id', 'club2_id'];
                foreach($clubs as $clubAttrName){
                    if(!empty($booking[$clubAttrName])){
                        if (!ctype_digit($booking[$clubAttrName])) {
                               $newClub = Club::where('name', '{"en":"'.$booking[$clubAttrName].'"}')->first();
                               if(empty($newClub)){
                                $newClub =  new Club();
                                $newClub->name =  json_encode([ "en"=> $booking[$clubAttrName]]);
                                $newClub->status  = Club::STATUS_PUBLISH;
                                $newClub->save();
                               }
                               $bModel->{$clubAttrName} = $newClub->id;
                        }else{
                            $bModel->{$clubAttrName} = $booking[$clubAttrName];
                        }
                    }
                }

                $bModel->match_referee_required = $booking['referee'];
                $bModel->club1_water_boxes = $booking['boxes'];
                $bModel->club2_water_boxes = $booking['boxes'];
                $bModel->fee_type = 'per_slot';
                $bModel->slot_fee_paid = $booking['slot_fee_paid'];
                $bModel->slot_fee_deposit_paid = $booking['slot_fee_deposit_paid'];
                $bModel->slot_fee = $booking['slot_fee'] ?  doubleval( $booking['slot_fee']):0.0;
                $bModel->save();
            }
        });

        return Helper::jsonMessage($blockBooking->id !== null, BlockBooking::INDEX_URL);
    }

    /**
     * @inheritDoc
     */
    public function edit($id)
    {
        $users = User::where('status',1)->pluck('name', 'id');
        $sports = Sport::where('status', 1)->pluck('name', 'id');
        $tournaments = Tournament::where('status', 1)->pluck('name', 'id');
        $clubs = Club::where('status',1)->pluck('name', 'id');
        $location = [];
        $stadiumFacilityQuery = StadiumFacility::with(['sport'])->where('status', 1);
        if (!empty(auth()->user()->stadium_id)) {
            $stadiumFacilityQuery->whereHas('stadium', function ($query) {
                $query->where('id', auth()->user()->stadium_id);
            });
        }
        $stadiumFacility = $stadiumFacilityQuery->get();
        foreach ($stadiumFacility as $sf) {
            $location[$sf->sport_id][$sf->id] = $sf->name . ', ' . $sf->stadium->name;
        }

        $item = BlockBooking::find($id);
        return view('pages.block-booking.add', [
            'title' => 'Edit Block Booking',
            'users' =>  $users,
            'sports' => $sports,
            'locations' => $location,
            'tournaments' => $tournaments,
            'item' => $item,
            'clubs'=> $clubs
        ]);
    }

    public function update($id, Request $request)
    {
        $this->validate($request, BlockBooking::$validation);

        $stadiumsByFacilityId = StadiumFacility::pluck('stadium_id', 'id');

        $blockBooking = BlockBooking::findOrFail($id);
        $blockBooking->fill($request->all());

        DB::transaction(function () use ($blockBooking, $request, $stadiumsByFacilityId) {

            $blockBooking->save();
            $bookings = json_decode($request->bookings, true);
            foreach ($bookings as $booking) {
                if(!empty($booking['bookingId'])){
                    $bModel =  Booking::find($booking['bookingId']);
                }else{
                    $bModel = new Booking();
                }

                $bModel->stadium_id = $stadiumsByFacilityId[$booking['location']];
                $bModel->stadium_facility_id = $booking['location'];
                $bModel->booking_date = $booking['bookingDate'];
                $bModel->tournament_id = $request->tournament_id;
                $bModel->contact_person_id = $blockBooking->contact_person_id;
                $bModel->start_time = $booking['startTime'];
                $bModel->end_time = $booking['endTime'];
                $bModel->block_booking_id = $blockBooking->id;
                $bModel->match_referee_required = !empty($booking['referee']);
                $bModel->club1_water_boxes = !empty($booking['boxes']);
                $bModel->club2_water_boxes =  !empty($booking['boxes']);

                $clubs = ['club1_id', 'club2_id'];
                foreach($clubs as $clubAttrName){
                    if(!empty($booking[$clubAttrName])){
                        if (!ctype_digit($booking[$clubAttrName])) {
                               $newClub = Club::where('name', '{"en":"'.$booking[$clubAttrName].'"}')->first();
                               if(empty($newClub)){
                                $newClub =  new Club();
                                $newClub->name =  json_encode([ "en"=> $booking[$clubAttrName]]);
                                $newClub->status  = Club::STATUS_PUBLISH;
                                $newClub->save();
                               }
                               $bModel->{$clubAttrName} = $newClub->id;
                        }else{
                            $bModel->{$clubAttrName} = $booking[$clubAttrName];
                        }
                    }
                }

                $bModel->fee_type = 'per_slot';
                $bModel->slot_fee = $booking['slot_fee'] ?  doubleval( $booking['slot_fee']):0.0;
                $bModel->slot_fee_paid = !empty($booking['slot_fee_paid']);
                $bModel->slot_fee_deposit_paid = !empty($booking['slot_fee_deposit_paid']);
                $bModel->save();
            }
        });


        return Helper::jsonMessage($blockBooking->id !== null, BlockBooking::INDEX_URL);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {

        $blockBooking = BlockBooking::query()->findOrFail($id);
        try {
            foreach($blockBooking->bookings as $booking){
                if($booking->match !== null){
                    $booking->match->delete();
                }
                $booking->delete();
            }

            $blockBooking->delete();
            return Helper::jsonMessage(true, NULL, 'Record Successfully deleted');
        } catch (Exception $e) {
            return Helper::jsonMessage(false, NULL, $e->getMessage());
        }
    }

    public function listAvailableSlots(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sportId' => 'required',
            'facilityIds' => 'sometimes|array',
            'startDate' => 'required',
            'endDate' => 'required',
        ]);

        if ($validator->fails()) {
            dd("validation failed", $validator->errors());
        }

        $sportId = $request->sportId;
        $facilityIds = $request->get('facilityIds', null);

        $startDate = Carbon::createFromFormat('Y-m-d', $request->startDate);
        $endDate = Carbon::createFromFormat('Y-m-d', $request->endDate);

        $stadiumFacilities = StadiumFacility::where('status', 1)
            ->where('sport_id', $sportId)
            ->when($facilityIds, function ($query, $facilityIds) {
                $query->whereIn('id', $facilityIds);
            })->get();

        $allSlots = [];
        foreach ($stadiumFacilities as $sf) {
            $slots = $sf->availableSlots($startDate, $endDate);
            $allSlots = [...$allSlots, ... $slots];

        }

        $groupedSlots = [];
        foreach ($allSlots as $slot) {

            $groupedSlots[$slot['only_day'] . ' ' . $slot['only_date']][$slot['sf_id']][] = $slot;
        }

        return $groupedSlots;
    }

    public function deleteSingleBooking(Request  $request){
        $this->validate($request,[
            'booking_id'=> 'required',
        ]);
        $booking = Booking::findOrFail($request->booking_id);

        $booking->delete();
        return [
            "success"=>true
        ];
    }
}
