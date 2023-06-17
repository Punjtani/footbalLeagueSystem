<?php

namespace App\Http\Controllers;

use App\Booking;
use App\BookingRule;
use App\Club;
use App\Helpers\Helper;
use App\Helpers\HtmlTemplatesHelper;
use App\Sport;
use App\Stadium;
use App\StadiumFacility;
use App\OverridePricing;
use App\User;
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Monarobase\CountryList\CountryListFacade as Countries;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;


class OverridePricingController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return Factory|View
     */
    public function bookVenueOverridingPricing($id)
    {
        $facility = StadiumFacility::findOrFail($id);
        $admins = User::query()->where('status', 1)->get();
        $contacts = [];
        $phones = [];
        foreach ($admins as $admin) {
            $contacts[$admin->id] = $admin->name . ': ' . $admin->phone;
            $phones[$admin->id] = $admin->intl_phone;
        }


        $bookingRule = $facility->bookingRule;
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addMonth(3);
        $overridedPricings = OverridePricing::where('facility_id', $id)->where('slot_date', ">=", $startDate->format('Y-m-d'))
            ->where('slot_date', '<=', $endDate->format('Y-m-d'))->get();
        $overridedPricingChecker = [];
        foreach ($overridedPricings as $op) {
            $overridedPricingChecker[$op->slot_date][$op->slot_time_start][$op->slot_time_end] = $op->overrided_price;
        }


        $weeklySchedule = [];

        if ($bookingRule !== null) {
            $endDate = clone $startDate;
            $endDate->addMonths($bookingRule->booking_window_duration);
            $weeklySchedule = json_decode($bookingRule->weekly_schedule, true);
        }

        $weekMap = ['Mon' => 1, 'Tue' => 2, 'Wed' => 3, 'Thu' => 4, 'Fri' => 5, 'Sat' => 6, 'Sun' => 7];

        $loopStartDate = Carbon::now();
        $loopTill = clone $endDate;

        $slots = [];
        while ($loopStartDate <= $loopTill) {
            $day = $weekMap[$loopStartDate->format('D')];
            foreach ($weeklySchedule as $schedule) {
                if (in_array($day, $schedule['days'])) {

                    $from = $this->getFromDateTimeold($loopStartDate, $schedule['startTime']);

                    $to = $this->getFromDateTimeold($loopStartDate, $schedule['endTime']);

                    $slots[] = [
                        'start' => $from->format('Y-m-d H:i:s'),
                        'end' => $to->format('Y-m-d H:i:s'),
                        'price' => $schedule['price'],
                        'contact' => $this->buildContacts($schedule["contact"], $contacts, $phones),
                        //'intl_phone'=> !empty($phones[$schedule["contact"]]) ? $phones[$schedule["contact"]]: "",
                        'title' => $from->format('h:iA') . " - " . $to->format('h:iA'),
                        'only_date' => $from->format('Y-m-d'),
                        'type' => !empty($schedule['type']) ? $schedule['type'] : 'per_team',
                        'booking_status' => $this->getBookingStatusForFrontEnd($from->format('Y-m-d H:i:s')),
                        'overridedPrice' => !empty($overridedPricingChecker[$from->format('Y-m-d')][$from->format('H:i:s')][$to->format('H:i:s')]) ? $overridedPricingChecker[$from->format('Y-m-d')][$from->format('H:i:s')][$to->format('H:i:s')] : null,
                    ];
                }
            }
            $loopStartDate->addDay(1);
        }


        /// manually added slots



        $loopStartDate = Carbon::now();
        $loopTill = clone $endDate;

        $bookings = Booking::with(['club1', 'club2', 'stadiumFacility', 'stadiumFacility.sport', 'blockBooking'])
            ->where('stadium_id', $facility->stadium->id)
            ->where('stadium_facility_id', $facility->id)
            ->where(function ($query) {
                $query->orWhere(function ($query) {
                    $query->where('fee_type', 'per_team');
                    $query->where(function ($query) {
                        $query->orWhere('club1_payment_confirmed', true);
                        $query->orWhere('club2_payment_confirmed', true);
                    });
                });
                $query->orWhere(function ($query) {
                    $query->where('fee_type', 'per_slot');
                    $query->where('slot_fee_deposit_paid', true);
                });
            })
            ->where('booking_date', '>=', $loopStartDate)
            ->where('booking_date', '<=', $loopTill)->get();

        $bookingEvents = [];
        foreach ($bookings as $booking) {

            $bookingEvents[] = [
                'start' => $booking->booking_date . " " . $booking->start_time,
                'end' => $booking->booking_date . " " . $booking->end_time,
                'price' => $booking->fee_type == 'per_team' ? $booking->club1_fee : $booking->slot_fee,
                'contact' => $this->buildContacts($booking->contact_person_id, $contacts, $phones),
                //'intl_phone'=> !empty($phones[$booking->contact_person_id]) ? $phones[$booking->contact_person_id]: "",
                'title' => $booking->getTitle(),
                'only_date' => $booking->booking_date,
                'booking_status' => $booking->getBookingStatusForFrontEnd(),
                'block_booking_id' => $booking->block_booking_id,
                'type' => $booking->fee_type,
                'overridedPrice' => !empty($overridedPricingChecker[$booking->booking_date][$booking->start_time][$booking->end_time]) ? $overridedPricingChecker[$booking->booking_date][$booking->start_time][$booking->end_time] : null,

            ];
        }

        $nonOverlappingSlots = [];
        foreach ($slots as $slot) {
            $overlap = false;
            foreach ($bookings as $booking) {


                $slotStart = Carbon::createFromFormat('Y-m-d H:i:s', $slot['start']);
                $slotEnd = Carbon::createFromFormat('Y-m-d H:i:s', $slot['end']);
                if ($slotEnd < $slotStart) {
                    $slotEnd->addDays(1);
                }

                $bookingStart = Carbon::createFromFormat('Y-m-d H:i:s', $booking->booking_date . " " . $booking->start_time);
                $bookingEnd = Carbon::createFromFormat('Y-m-d H:i:s', $booking->booking_date . " " . $booking->end_time);
                if ($bookingEnd < $bookingStart) {
                    $bookingEnd->addDays(1);
                }


                $result = $this->datesOverlap($slotStart, $slotEnd, $bookingStart, $bookingEnd);

                if ($result > 0) {
                    $overlap = true;
                }
            }

            if (!$overlap) {
                $nonOverlappingSlots[] = $slot;
            }
        }
        $manuallyAddedSlots = \App\ManualSlot::where('facility_id', $id)->where('slot_date', '>=', $startDate->format('Y-m-d'))->where('slot_date', '<', $endDate->format('Y-m-d'))->get();
        foreach ($manuallyAddedSlots as $manualSlot) {

            $slotStart = Carbon::createFromFormat('Y-m-d H:i:s', $manualSlot->slot_date . ' ' . $manualSlot->slot_time_start);
            $slotEnd = Carbon::createFromFormat('Y-m-d H:i:s', $manualSlot->slot_date . ' ' . $manualSlot->slot_time_end);
            $nonOverlappingSlots[] = [
                'start' => $slotStart->format('Y-m-d H:i:s'),
                'end' => $slotEnd->format('Y-m-d H:i:s'),
                'price' => $manualSlot->price,
                'contact' => $this->buildContacts(explode(',', $manualSlot->contacts), $contacts, $phones),
                'contactIds' => $manualSlot->contacts,
                'title' => $slotStart->format('h:iA') . " - " . $slotEnd->format('h:iA'),
                'only_date' => $slotStart->format('Y-m-d'),
                'type' => $manualSlot->type,
                'booking_status' => $this->getBookingStatusForFrontEnd($slotStart),
                'overridedPrice' => null,
                'manualSlotId' => $manualSlot->id

            ];
        }
        return  [
            'facility' => $facility,
            'startDate' => $startDate,
            'endDate' => $endDate,
            //'bookingEvents' => $bookingEvents,
            'slots' => [...$bookingEvents, ...$nonOverlappingSlots],
            'isMobile' => $this->isMobile()
        ];
    }

    private function getToDateTimeold(Carbon $startDate, $endTime)
    {

        if ($endTime === '00:00') {
            // $endTime = '23:59';
            $startDate = clone $startDate;
            $startDate->addDays(1);
        }

        return Carbon::createFromFormat("Y-m-d H:i", $startDate->format('Y-m-d') . " " . $endTime);
    }

    private function getToDateTime($startDate, $endTime)
    {

        if ($endTime === '00:00') {
            // $endTime = '23:59';
            $startDate = clone $startDate;
            $startDate->addDays(1);
        }

        return Carbon::createFromFormat("Y-m-d H:i", $startDate . " " . $endTime);
    }

    private function getFromDateTimeold(Carbon $startDate, $startTime)
    {
        return Carbon::createFromFormat("Y-m-d H:i", $startDate->format('Y-m-d') . " " . $startTime);
    }

    private function getFromDateTime($startDate, $startTime)
    {
        return Carbon::createFromFormat("Y-m-d H:i", $startDate . " " . $startTime);
    }

    public function getBookingStatusForFrontEnd($start_time)
    {
        // if a booking is in past just show it in red booked
        if ($start_time < Carbon::now()) {
            return 3;
        } else {
            return 0;
        }
    }
    function isMobile()
    {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
    }

    public function buildContacts($contactIds, $displayNames, $phones)
    {
        if (!is_array($contactIds)) {
            $contactIds = [$contactIds];
        }

        $contacts = [];
        foreach ($contactIds as $contactId) {
            if (!empty($displayNames[$contactId]) && !empty($phones[$contactId]))
                $contacts[] = [
                    'displayName' => $displayNames[$contactId],
                    'intl_phone' => $phones[$contactId],
                ];
        }
        return $contacts;
    }

    public function index($id = null)
    {

        ini_set('max_execution_time', '1200');
        $pagesController = new  \App\Http\Controllers\FrontEnd\PagesController();
        $stadiumOptions = Stadium::where('status', 1)->pluck('name', 'id');
        $sportsOptions = Sport::where('status', 1)->pluck('name', 'id');
        $stadiumFacilities = StadiumFacility::where('status', 1)->get();
        $admins = \App\User::where('status', 1)->pluck('name', 'id');

        if ($id === null) {
            return view('pages.override-pricing.index', [
                'title' => 'Override Price',
                'sportsOptions' => $sportsOptions,
                'stadiumOptions' => $stadiumOptions,
                'stadiumFacilities' => $stadiumFacilities,
                'facilityId' => $id,
                'slots' => [],
                'admins' => $admins
            ]);
        }


        $facility = \App\StadiumFacility::where('id', intval($id))->first();
        $data = $this->bookVenueOverridingPricing(intval($id));
        $data = array_merge($data, ['sportsOptions' => $sportsOptions,
            'title' => 'Override Price',
            'stadiumOptions' => $stadiumOptions,
            'stadiumFacilities' => $stadiumFacilities,
            'facilityId' => $id,
            'stadiumId' => $facility->stadium_id,
            'admins' => $admins]);

        foreach ($data['slots'] as &$slot) {
            $slot['editMode'] = false;
        }

        return view('pages.override-pricing.index', $data);

    }

    private function advance_filters(): array
    {
        $club_list = Club::query()->select('id', 'name')->where('status', Club::STATUS_PUBLISH)->get()->toArray();
        $clubs = array();
        foreach ($club_list as $club) {
            $clubs[$club['id']] = $club['name'];
        }
        return array(
            'Club' => array('type' => 'dropdown', 'sub_type' => '', 'placeholder' => 'Select Club', 'name' => 'club_id', 'data' => $clubs),
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Factory|View
     */
    public function create()
    {
        $stadiumOptions = Stadium::where('status', 1)->pluck('name', 'id');
        $sportsOptions = Sport::where('status', 1)->pluck('name', 'id');
        $stadiumFacilities = StadiumFacility::where('status', 1)->get()->pluck('title', 'id');

        return view('pages.last-minute-bookings.add', [
            'title' => 'Add Last Minute Booking Rules',
            'breadcrumbs' => Breadcrumbs::generate('last-minute-bookings.create'),
            'sportsOptions' => $sportsOptions,
            'stadiumOptions' => $stadiumOptions,
            'stadiumFacilities' => $stadiumFacilities,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {

        request()->validate([
            'facility_id' => 'required',
            'slot_date' => 'required',
            'slot_time_start' => 'required',
            'slot_time_end' => 'required',
            'overrided_price' => 'required',
        ]);

        $op = OverridePricing::where('facility_id', $request->facility_id)
            ->where('slot_date', $request->slot_date)->where('slot_time_start', $request->slot_time_start)
            ->where('slot_time_end', $request->slot_time_end)
            ->first();

        if ($op === null) {
            $op = new OverridePricing;
        }

        $op->fill($request->all());
        $op->save();


        return Helper::jsonMessage(true);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request)
    {

        request()->validate([
            'facility_id' => 'required',
            'slot_date' => 'required',
            'slot_time_start' => 'required',
            'slot_time_end' => 'required',
        ]);

        OverridePricing::where('facility_id', $request->facility_id)
            ->where('slot_date', $request->slot_date)->where('slot_time_start', $request->slot_time_start)
            ->where('slot_time_end', $request->slot_time_end)
            ->delete();


        return Helper::jsonMessage(true);
    }

    public function addManualSlot(Request $request)
    {
        ini_set('max_execution_time', '1200');
        $pagesController = new  \App\Http\Controllers\FrontEnd\PagesController();

        $allowedRangeStartDate = Carbon::now()->addDay(-1);
        $allowedRangeEndDate = Carbon::now()->addMonth(3);

//        'facility_id' => ['required',
//        Rule::unique('manual_slots')->where(function ($query) use ($request) {
//            return $query->where([
//                'facility_id' => $request->facility_id,
//                'slot_date' => $request->slot_date,
//                'slot_time_start' => $request->slot_time_start,
//                'slot_time_end' => $request->slot_time_end,
//                'price' => $request->price,
//                'type' => $request->type
//            ]);
//        })],
        request()->validate([
            'facility_id' => ['required',
                Rule::unique('manual_slots')->where(function ($query) use ($request) {
                    return $query->where([
                        'facility_id' => $request->facility_id,
                        'slot_date' => $request->slot_date,
                        'slot_time_start' => $request->slot_time_start,
                        'slot_time_end' => $request->slot_time_end,
                        'price' => $request->price,
                        'type' => $request->type
                    ]);
                })],
            'slot_date' => 'required|date|after:' . $allowedRangeStartDate->format('Y-m-d 23:59:59') . '|before:' . $allowedRangeEndDate->format('Y-m-d'),
            'slot_time_start' => 'required',
            'slot_time_end' => 'required',
            'price' => 'required|numeric',
            'contacts' => 'required|array',
            'type' => 'required'
        ], [
            'slot_date.after' => 'Please choose a date from today and within 3 months in futures',
            'slot_date.before' => 'Please choose a date from today and within 3 months in futures',
            'facility_id.unique' => 'Given Data already exist in your database.'
        ]);


        $start = Carbon::createFromFormat('Y-m-d H:i:s', $request->slot_date . ' ' . $request->slot_time_start . ':00');
        $end = Carbon::createFromFormat('Y-m-d H:i:s', $request->slot_date . ' ' . $request->slot_time_end . ':00');

        $facility = StadiumFacility::find($request->facility_id);

        // checking bookings
        $bookings = \App\Booking::where('stadium_facility_id', $request->facility_id)
            ->where('booking_date', $start->format('Y-m-d'))
            ->where('start_time', '<', $end->format('H:i:00'))
            ->where('end_time', '>', $start->format('H:i:00'))->get();


        if (count($bookings) > 0) {
            throw ValidationException::withMessages(['slot' => 'Your given slot is already booked. ']);
        }

        // check if a slot already exists for this ;
        $slotData = $pagesController->bookVenueOverridingPricing(intval($request->facility_id))->getData()['slots'];

        foreach ($slotData as $slot) {
            $slotStart = Carbon::createFromFormat('Y-m-d H:i:s', $slot['start']);
            $slotEnd = Carbon::createFromFormat('Y-m-d H:i:s', $slot['end']);

            if ($this->datesOverlap($slotStart, $slotEnd, $start, $end) && !empty($slot['manualSlotId']) && intval($slot['manualSlotId']) !== intval($request->manualSlotId)) {
                throw ValidationException::withMessages(['slot' => 'Your given slot is overlapping with another slot']);
            }
        }

        if ($request->manualSlotId > 0) {
            $manualSlot = \App\ManualSlot::where('id', $request->manualSlotId)->first();
        } else {
            $manualSlot = new \App\ManualSlot();
        }

        $manualSlot->fill([
            'facility_id' => $request->facility_id,
            'slot_date' => $request->slot_date,
            'slot_time_start' => $request->slot_time_start,
            'slot_time_end' => $request->slot_time_end,
            'price' => $request->price,
            'type' => $request->type
        ]);
        $manualSlot->contacts = join(',', $request->contacts);

        $manualSlot->save();

        return [];

    }

    function deleteManualSlot(Request $request)
    {
        request()->validate([
            'id' => 'required',
        ]);

        $manualSlot = \App\ManualSlot::where('id', $request->id)->first();
        if ($manualSlot !== null) {
            $manualSlot->delete();
        }
        return [];
    }

    function datesOverlap($start_one, $end_one, $start_two, $end_two)
    {

        if ($start_one < $end_two && $end_one > $start_two) { //If the dates overlap
            return min($end_one, $end_two)->diff(max($start_two, $start_one))->days + 1; //return how many days overlap
        }

        return 0; //Return 0 if there is no overlap
    }
}
