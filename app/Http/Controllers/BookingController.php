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
use App\Tournament;
use App\User;
use Carbon\Carbon;
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Monarobase\CountryList\CountryListFacade as Countries;
class BookingController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }
   
    public function index()
    {
        try {
            if (request()->ajax()) {
                $query = Booking::filters(request());
                if (!empty(auth()->user()->stadium_id)) {
                    $query->where('stadium_id', auth()->user()->stadium_id);
                }
                $query->whereNull('block_booking_id');
                return datatables($query)
                    ->addColumn('actions', static function ($data) {
                        // ->can('Bookings.Update'),auth()->user()->can('Bookings.Duplicate')
                        return HtmlTemplatesHelper::get_action_dropdown($data, '', false, false, auth()->user()->can('Bookings.Update'),auth()->user()->can('Bookings.Delete'),auth()->user()->can('Bookings.Update'),false);
                    })
                    ->addColumn('addons', function ($data) {
                        $final = [];
                        if (!empty($data->match_referee_required)) {
                            $final[] = "<strong style='font-size: 0.9em'>Referee: </strong>Yes";
                        } else {
                            $final[] = "<strong style='font-size: 0.9em'>Referee: </strong>No";
                        }

                        if (!empty($data->club1_water_boxes)) {
                            $final[] = "<strong style='font-size: 0.9em'>Team A Water-Box: </strong>Yes";
                        } else {
                            $final[] = "<strong style='font-size: 0.9em'>Team A Water-Box: </strong>No";
                        }

                        if (!empty($data->club2_water_boxes)) {
                            $final[] = "<strong style='font-size: 0.9em'>Team B Water Box: </strong>Yes";
                        } else {
                            $final[] = "<strong style='font-size: 0.9em'>Team B Water Box: </strong>No";
                        }

                        return "<span style='font-size: 0.8em'>" . join("<br />", $final) . "</span>";
                    })
                    ->addColumn('timing_detail', static function ($data) {
                        $startTime = Carbon::createFromFormat('Y-m-d H:i:s', "{$data->booking_date} {$data->start_time}");
                        $endTime = Carbon::createFromFormat('Y-m-d H:i:s', "{$data->booking_date} {$data->end_time}");
                        return "<strong>{$startTime->format('d/m/Y')}</strong><br /> <span style='font-size: 0.8em'>{$startTime->format('h:iA')} - {$endTime->format('h:iA')}</span>";
                    })->addColumn('teams', static function ($data) {
                        $vs = [];
                        if ($data->club1 !== null)
                            $vs[] = $data->club1->name;

                        if ($data->club2 !== null)
                            $vs[] = $data->club2->name;
                        return "<strong>" . implode(" vs ", $vs) . "</strong><br /> <span style='font-size:0.8em'>{$data->tournament->name}</span>";
                    })->addColumn('location', static function ($data) {
                        $stadiumName = optional($data->stadium)->name;
                        $facilityTitle = optional($data->stadiumFacility)->title;
                        return "
                        <span style='font-size: 0.9em'>{$facilityTitle}</span>
                        <br />
                        <span style='font-size: 0.8em'>{$stadiumName}</span>

                        ";
                    })->addColumn('booking_fee', static function ($data) {

                        if ($data->fee_type === 'per_slot') {
                            return 'RM' . $data->slot_fee;
                        }

                        $payment = [];
                        if ($data->club1 !== null) {
                            $payment[] = "<strong style='font-size: 0.9em'>Team A</strong> - RM{$data->club1_fee}";
                        }
                        if ($data->club2 !== null) {
                            $payment[] = "<strong style='font-size: 0.9em'>Team B</strong> - RM{$data->club2_fee}";
                        }
                        $total = $data->club1_fee + $data->club2_fee;
                        $payment[] = "<strong style='font-size: 0.9em'>Total &nbsp;&nbsp;&nbsp;&nbsp;</strong> - RM{$total}";


                        return implode("<br />", $payment);
                    })
                    ->addColumn('payment', static function ($data) {
                        $payment = [];
                        if ($data->fee_type === 'per_slot') {
                            if ($data->slot_fee_paid) {
                                $payment[] = "<strong style='font-size: 0.8em'>Fully Paid</strong>: RM" . $data->slot_fee;
                            } else if ($data->slot_fee_deposit_paid) {
                                $payment[] = "<strong style='font-size: 0.8em'>Deposited</strong>: RM" . $data->slot_deposit;
                                if (!empty($data->slot_fee)) {
                                    $payment[] = "<strong style='font-size: 0.8em'>Pending Amount</strong>: RM" . ($data->slot_fee - $data->slot_deposit);
                                }
                            } else {
                                $payment[] = "<strong style='font-size: 0.8em'>Pending Deposit/Amount</strong>: RM" . ($data->slot_fee);

                            }
                        } else {
                            if ($data->club1 !== null) {
                                if ($data->club1_fully_paid) {
                                    $payment[] = "<strong style='font-size: 0.8em'>Team A - Fully Paid</strong>: RM" . $data->club1_fee;
                                } else if ($data->club1_payment_confirmed) {
                                    $payment[] = "<strong style='font-size: 0.8em'>Team A - Deposited</strong>: RM" . $data->club1_deposit_amount;
                                    $payment[] = "<strong style='font-size: 0.8em'>Team A - Pending Amount</strong>: RM" . ($data->club1_fee - $data->club1_deposit_amount);
                                } else {
                                    $payment[] = "<strong style='font-size: 0.8em'>Team A - Pending Deposit</strong>: RM" . $data->club1_deposit_amount;
                                }

                            }
                            if ($data->club2 !== null) {
                                if ($data->club2_fully_paid) {
                                    $payment[] = "<strong style='font-size: 0.8em'>Team B - Fully Paid</strong>: RM" . $data->club2_fee;
                                } else if ($data->club1_payment_confirmed) {
                                    $payment[] = "<strong style='font-size: 0.8em'>Team B - Deposited</strong>: RM" . $data->club2_deposit_amount;
                                    $payment[] = "<strong style='font-size: 0.8em'>Team B - Pending Amount</strong>: RM" . ($data->club2_fee - $data->club2_deposit_amount);
                                } else {
                                    $payment[] = "<strong style='font-size: 0.8em'>Team B - Pending Deposit</strong>: RM" . $data->club2_deposit_amount;
                                }
                            }
                        }


                        return implode("<br />", $payment);
                    })
                    ->addColumn('booking_status', static function ($data) {
                        return $data->bookingStatusX();
                    })
                    ->rawColumns(['addons', 'booking_fee', 'booking_status', 'payment', 'teams', 'location', 'timing_detail', 'actions', 'status'])
                    ->make(true);
            }
        } catch (Exception $ex) {
        }
        return view('pages.bookings.index', ['add_url' => route('bookings.create'), 'filterData' => $this->advance_filters(), 'breadcrumbs' => Breadcrumbs::generate('bookings')]);
    }


    public function bookingUnpaid()
    {
        try {
            if (request()->ajax()) {
                $query = Booking::unpaidBooking();
                $query->whereNull('block_booking_id');
                if (!empty(auth()->user()->stadium_id)) {
                    $query->where('stadium_id', auth()->user()->stadium_id);
                }
                return datatables($query->with(['stadium', 'stadiumFacility', 'club1', 'club2']))
                    ->addColumn('actions', static function ($data) {
                        return HtmlTemplatesHelper::get_action_dropdown($data, '', false, false, auth()->user()->can('Bookings.Update'), auth()->user()->can('Bookings.Delete'));
                    })
                    ->addColumn('addons', function ($data) {
                        $final = [];
                        if (!empty($data->match_referee_required)) {
                            $final[] = "<strong style='font-size: 0.9em'>Referee: </strong>Yes";
                        } else {
                            $final[] = "<strong style='font-size: 0.9em'>Referee: </strong>No";
                        }

                        if (!empty($data->club1_water_boxes)) {
                            $final[] = "<strong style='font-size: 0.9em'>Team A Water-Box: </strong>Yes";
                        } else {
                            $final[] = "<strong style='font-size: 0.9em'>Team A Water-Box: </strong>No";
                        }

                        if (!empty($data->club2_water_boxes)) {
                            $final[] = "<strong style='font-size: 0.9em'>Team B Water Box: </strong>Yes";
                        } else {
                            $final[] = "<strong style='font-size: 0.9em'>Team B Water Box: </strong>No";
                        }

                        return "<span style='font-size: 0.8em'>" . join("<br />", $final) . "</span>";
                    })
                    ->addColumn('timing_detail', static function ($data) {
                        $startTime = Carbon::createFromFormat('Y-m-d H:i:s', "{$data->booking_date} {$data->start_time}");
                        $endTime = Carbon::createFromFormat('Y-m-d H:i:s', "{$data->booking_date} {$data->end_time}");
                        return "<strong>{$startTime->format('d/m/Y')}</strong><br /> <span style='font-size: 0.8em'>{$startTime->format('h:iA')} - {$endTime->format('h:iA')}</span>";
                    })->addColumn('teams', static function ($data) {
                        $vs = [];
                        if ($data->club1 !== null)
                            $vs[] = $data->club1->name;

                        if ($data->club2 !== null)
                            $vs[] = $data->club2->name;
                        return "<strong>" . implode(" vs ", $vs) . "</strong><br /> <span style='font-size:0.8em'>{$data->tournament->name}</span>";
                    })->addColumn('location', static function ($data) {
                        $stadiumName = optional($data->stadium)->name;
                        $facilityTitle = optional($data->stadiumFacility)->title;
                        return "
                        <span style='font-size: 0.9em'>{$facilityTitle}</span>
                        <br />
                        <span style='font-size: 0.8em'>{$stadiumName}</span>

                        ";
                    })->addColumn('booking_fee', static function ($data) {
                        $payment = [];
                        if ($data->club1 !== null) {
                            $payment[] = "<strong style='font-size: 0.9em'>Team A</strong> - RM{$data->club1_fee}";
                        }
                        if ($data->club2 !== null) {
                            $payment[] = "<strong style='font-size: 0.9em'>Team B</strong> - RM{$data->club2_fee}";
                        }
                        $total = $data->club1_fee + $data->club2_fee;
                        $payment[] = "<strong style='font-size: 0.9em'>Total &nbsp;&nbsp;&nbsp;&nbsp;</strong> - RM{$total}";


                        return implode("<br />", $payment);
                    })
                    ->addColumn('payment', static function ($data) {
                        $payment = [];
                        if ($data->club1 !== null) {
                            if ($data->club1_fully_paid) {
                                $payment[] = "<strong style='font-size: 0.8em'>Team A - Fully Paid</strong>: RM" . $data->club1_fee;
                            } else if ($data->club1_payment_confirmed) {
                                $payment[] = "<strong style='font-size: 0.8em'>Team A - Deposited</strong>: RM" . $data->club1_deposit_amount;
                                $payment[] = "<strong style='font-size: 0.8em'>Team A - Pending Amount</strong>: RM" . ($data->club1_fee - $data->club1_deposit_amount);
                            } else {
                                $payment[] = "<strong style='font-size: 0.8em'>Team A - Pending Deposit</strong>: RM" . $data->club1_deposit_amount;
                            }

                        }
                        if ($data->club2 !== null) {
                            if ($data->club2_fully_paid) {
                                $payment[] = "<strong style='font-size: 0.8em'>Team B - Fully Paid</strong>: RM" . $data->club2_fee;
                            } else if ($data->club1_payment_confirmed) {
                                $payment[] = "<strong style='font-size: 0.8em'>Team B - Deposited</strong>: RM" . $data->club2_deposit_amount;
                                $payment[] = "<strong style='font-size: 0.8em'>Team B - Pending Amount</strong>: RM" . ($data->club2_fee - $data->club2_deposit_amount);
                            } else {
                                $payment[] = "<strong style='font-size: 0.8em'>Team B - Pending Deposit</strong>: RM" . $data->club2_deposit_amount;
                            }
                        }


                        return implode("<br />", $payment);
                    })
                    ->addColumn('booking_status', static function ($data) {
                        return $data->bookingStatusX();
                    })
                    ->rawColumns(['addons', 'booking_fee', 'booking_status', 'payment', 'teams', 'location', 'timing_detail', 'actions', 'status'])
                    ->make(true);
            }
        } catch (Exception $ex) {
        }
        return view('pages.bookings.uncollected-booking', ['add_url' => route('bookings.create'), 'filterData' => $this->advance_filters(), 'breadcrumbs' => Breadcrumbs::generate('bookings')]);
    }

    private function advance_filters(): array
    {
        $users = User::pluck('name', 'id');

        return [
            'users' => $users,
            'stadiums' => Stadium::pluck('name', 'id'),
            'sports' => Sport::pluck('name', 'id')
        ];
    }

    public function create()
    {
        $stadiums = [];
        if (!empty(request()->user()->stadium_id)) {
            $stadiums[request()->user()->stadium_id] = request()->user()->stadium->name;
        } else {
            $stadiums = Stadium::where('status', 1)->pluck('name', 'id');
        }


        $facilities = [];
        $stadiumFacilities = StadiumFacility::with(['sport'])->where('status', 1)->get();

        foreach ($stadiumFacilities as $stadiumFacility) {
            $facilities[$stadiumFacility->stadium_id][$stadiumFacility->id] = $stadiumFacility->sport->name . "-" . $stadiumFacility->name;
        }

        $tournaments = Tournament::where('status', 1)->pluck('name', 'id');
        $clubs = Club::where('status', 1)->pluck('name', 'id');
        $users = User::where('status', 1)->pluck('name', 'id');


        return view('pages.bookings.add', [
            'title' => 'Add Booking',
            'stadiums' => $stadiums,
            'facilities' => $facilities,
            'tournaments' => $tournaments,
            'clubs' => $clubs,
            'users' => $users,
            'breadcrumbs' => Breadcrumbs::generate('bookings.create')
        ]);
    }

    public function edit($id,$type = null)
    {
        $booking = Booking::with('tournament')->findOrFail($id);
        $stadiums = [];
        if (!empty(request()->user()->stadium_id)) {
            $stadiums[request()->user()->stadium_id] = request()->user()->stadium->name;
        } else {
            $stadiums = Stadium::where('status', 1)->pluck('name', 'id');
        }


        $facilities = [];
        $stadiumFacilities = StadiumFacility::with(['sport'])->where('status', 1)->get();

        foreach ($stadiumFacilities as $stadiumFacility) {
            $facilities[$stadiumFacility->stadium_id][$stadiumFacility->id] = $stadiumFacility->sport->name . "-" . $stadiumFacility->name;
        }

        $tournaments = Tournament::where('status', 1)->pluck('name', 'id');
        $clubs = Club::where('status', 1)->pluck('name', 'id');
        $users = User::where('status', 1)->pluck('name', 'id');

        return view('pages.bookings.add', [
            'title' => 'Edit Booking: ' . $booking->id,
             'type' => $type,
            'item' => $booking,
            'stadiums' => $stadiums,
            'facilities' => $facilities,
            'tournaments' => $tournaments,
            'clubs' => $clubs,
            'users' => $users,
            'breadcrumbs' => Breadcrumbs::generate('bookings.edit', $booking)
        ]);
    }

    public function getBookingsAndSlots(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'stadium_id' => 'required',
            'stadium_facility_id' => 'required',
            'booking_date' => 'required',
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        $bookingRule = StadiumFacility::findOrFail($request->stadium_facility_id)->bookingRule;
        $weeklySchedule = json_decode($bookingRule->weekly_schedule, true);
        $bookingDate = Carbon::createFromFormat('Y-m-d', $request->booking_date);
        $bookingWindow = Carbon::now()->add($bookingRule->booking_window_duration, 'months');
        $bookings = Booking::with(['club1', 'club2'])
            ->where('stadium_id', $request->stadium_id)
            ->where('stadium_facility_id', $request->stadium_facility_id)
            ->where('booking_date', $request->booking_date)->get();
        $overridedPricings = \App\OverridePricing::where('facility_id', $request->stadium_facility_id)
            ->where('slot_date', $request->booking_date)->get();
        $overridedPricingChecker = [];
        foreach ($overridedPricings as $op) {
            $overridedPricingChecker[$op->slot_date][$op->slot_time_start][$op->slot_time_end] = $op->overrided_price;
        }

        //dd($overridedPricingChecker);
        // there is no slots defined after booking window. so send no slots.
        if ($bookingDate > $bookingWindow) {
            return [
                "bookings" => $bookings,
                "slots" => [],
            ];
        }

        $weekMap = ['Mon' => 1, 'Tue' => 2, 'Wed' => 3, 'Thu' => 4, 'Fri' => 5, 'Sat' => 6, 'Sun' => 7];
        $day = $weekMap[$bookingDate->format('D')];

        $slots = [];

        foreach ($weeklySchedule as $schedule) {
            if (in_array($day, $schedule['days'])) {
                $slots[] = [
                    'from' => $schedule['startTime'],
                    'to' => $schedule['endTime'],
                    'price' => !empty($overridedPricingChecker[$request->booking_date][$schedule['startTime'] . ":00"][$schedule['endTime'] . ":00"]) ? $overridedPricingChecker[$request->booking_date][$schedule['startTime'] . ":00"][$schedule['endTime'] . ":00"] : $schedule['price'],
                    'contact' => $schedule["contact"],
                    'type' => $schedule['type'] ?? 'per_team',
                ];
            }
        }

        $manuallyAddedSlots = \App\ManualSlot::where('slot_date', '=', $request->booking_date)->where('facility_id', $request->stadium_facility_id)->get();
        foreach ($manuallyAddedSlots as $manualSlot) {

            $slotStart = Carbon::createFromFormat('Y-m-d H:i:s', $manualSlot->slot_date . ' ' . $manualSlot->slot_time_start);
            $slotEnd = Carbon::createFromFormat('Y-m-d H:i:s', $manualSlot->slot_date . ' ' . $manualSlot->slot_time_end);
            $slots[] = [
                'from' => $slotStart->format('h:i'),
                'to' => $slotEnd->format('h:i'),
                'price' => $manualSlot->price,
                'contact' => explode(',', $manualSlot->contacts),
                'type' => $manualSlot->type,
            ];
        }


        // find over lap
        $nonOverlappingSlots = [];
        foreach ($slots as $slot) {
            $overlap = false;
            foreach ($bookings as $booking) {

                $slotStart = floatval(str_replace(":", ".", $slot['from']));
                $slotEnd = floatval(str_replace(":", ".", $slot['to']));

                $bookingStart = floatval(str_replace(":", ".", $booking->start_time));
                $bookingEnd = floatval(str_replace(":", ".", $booking->end_time));


                $result = max(0, min($slotEnd, $bookingEnd) - max($slotStart, $bookingStart) + 1);
                if ($result > 0) {
                    $overlap = true;
                }
            }

            if (!$overlap) {
                $nonOverlappingSlots[] = $slot;
            }
        }


        return [
            'bookings' => $bookings,
            'slots' => $nonOverlappingSlots,
        ];
    }

    public function store(Request $request)
    {
         $rules = Booking::$validation;
         request()->validate($rules);

        $checkboxes = [
            'match_referee_required',
            'club1_payment_confirmed', 'club2_payment_confirmed',
            'club1_fully_paid', 'club2_fully_paid',
            'club1_water_boxes', 'club2_water_boxes',
            'slot_fee_paid', 'slot_fee_deposit_paid'
        ];
        foreach ($checkboxes as $checkbox) {
            if (empty($request->get($checkbox))) {
                $request->request->add([$checkbox => false]);
            }
        }


        // check if club is new , if new add
        if (!empty($request->club1_id)) {
            if (!ctype_digit($request->club1_id)) {
                $newClub = new Club();
                $newClub->name = json_encode(["en" => $request->club1_id]);
                $newClub->status = Club::STATUS_PUBLISH;
                $newClub->save();
                $request->request->add(['club1_id' => $newClub->id]);
            }
        }

        if (!empty($request->club2_id)) {
            if (!ctype_digit($request->club2_id)) {
                $newClub = new Club();
                $newClub->name = json_encode(["en" => $request->club2_id]);
                $newClub->status = Club::STATUS_PUBLISH;
                $newClub->save();
                $request->request->add(['club2_id' => $newClub->id]);
            }
        }

        $booking = new Booking();
        $booking->fill($request->all());
        $booking->save();


        return Helper::jsonMessage($booking->id !== null, Booking::INDEX_URL);

    }

    public function update($id, Request $request)
    {
        $rules = Booking::$validation;
        request()->validate($rules, [
            'club1_payment_confirmed.required_with' => "If Team A has fully paid, Please mark Team A - Deposit Paid as well.",
            'club2_payment_confirmed.required_with' => "If Team B has fully paid, Please mark Team B - Deposit Paid as well."
        ]);


        $booking = Booking::findOrFail($id);
        $checkboxes = [
            'match_referee_required',
            'club1_payment_confirmed', 'club2_payment_confirmed',
            'club1_fully_paid', 'club2_fully_paid',
            'club1_water_boxes', 'club2_water_boxes',
            'slot_fee_paid', 'slot_fee_deposit_paid'
        ];


        // check if club is new , if new add
        if (!empty($request->club1_id)) {
            if (!ctype_digit($request->club1_id)) {
                $newClub = new Club();
                $newClub->name = json_encode(["en" => $request->club1_id]);
                $newClub->status = Club::STATUS_PUBLISH;
                $newClub->save();
                $request->request->add(['club1_id' => $newClub->id]);
            }
        }

        if (!empty($request->club2_id)) {
            if (!ctype_digit($request->club2_id)) {
                $newClub = new Club();
                $newClub->name = json_encode(["en" => $request->club2_id]);
                $newClub->status = Club::STATUS_PUBLISH;
                $newClub->save();
                $request->request->add(['club2_id' => $newClub->id]);
            }
        }

        foreach ($checkboxes as $checkbox) {
            if (empty($request->get($checkbox))) {
                $request->request->add([$checkbox => false]);
            }
        }

        $booking->fill($request->all());

        $booking->save();

        return Helper::jsonMessage($booking->id !== null, Booking::INDEX_URL);

    }


    public function relatedVenues(Request $request)
    {

        $start = Carbon::createFromFormat('Y-m-d H:i:s', $request->start);
        $end = Carbon::createFromFormat('Y-m-d H:i:s', $request->end);

        $facility = StadiumFacility::find($request->facility_id);
        $otherFacilitiesQuery = StadiumFacility::with(['stadium', 'sport'])->where('id', '!=', $facility->id)
            ->where('sport_id', $facility->sport_id)->where('status', 1)->whereHas('stadium', function ($query) {
                $query->where('status', 1);
            });


        $otherFacilitiesIds = $otherFacilitiesQuery->pluck('id');
        $otherFacilities = $otherFacilitiesQuery->get();


        $bookingsQuery = Booking::whereIn('stadium_facility_id', $otherFacilitiesIds)
            ->where('booking_date', $start->format('Y-m-d'))
            ->where('start_time', '>=', $start->format('H:i:00'))
            ->where('end_time', '<=', $end->format('H:i:00'));


        $bookedFacilityIds = $bookingsQuery->pluck('stadium_facility_id');
        $bookings = $bookingsQuery->get();

        $response = [];
        foreach ($bookings as $booking) {
            foreach ($otherFacilities as $otherFacility) {
                if ($otherFacility->id !== $booking->stadium_facility_id) {
                    $response[] = $otherFacility;
                } elseif ($otherFacility->id === $booking->stadium_facility_id && $booking->getBookingStatusForFrontEnd() == 1) {
                    $response[] = $otherFacility;
                }
//                if (!in_array($otherFacility->id, $bookedFacilityIds->toArray())) {
//                    $response[] = $otherFacility;
//                } else {
//
//                    if ($booking->getBookingStatusForFrontEnd() == 1) {
//                        $response[] = $otherFacility;
//                    }
//
//                }
            }
        }

        return [
            "data" => $this->returnUniqueProperty($response,'id')
        ];
    }
    public static function returnUniqueProperty($array, $property)
    {
        $tempArray = array_unique(array_column($array, $property));
        $moreUniqueArray = array_values(array_intersect_key($array, $tempArray));
        return $moreUniqueArray;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {

        $booking = Booking::query()->findOrFail($id);

        if ($booking->match !== null) {
            $booking->match->delete();
        }

        try {
            $booking->delete();
            return Helper::jsonMessage(true, NULL, 'Record Successfully deleted');
        } catch (\Exception $e) {
            return Helper::jsonMessage(false, NULL, $e->getMessage());
        }
    }
}
