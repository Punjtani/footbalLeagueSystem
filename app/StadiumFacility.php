<?php

namespace App;



use Carbon\Carbon;

class StadiumFacility extends BaseModel
{
    protected $table = 'stadium_facilities';

    protected  $fillable = [
        'sport_id', 'name', 'stadium_id', 'booking_rule_id',
    ];

    protected $appends = [
        'title'
    ];

    public function sport(){
        return $this->belongsTo(Sport::class);
    }

    public function stadium(){
        return $this->belongsTo(Stadium::class);
    }

    public function bookingRule(){
        return $this->belongsTo(BookingRule::class);
    }
    public function getTitleAttribute(){
        return $this->sport->name." - ".$this->name;
    }

    public function availableSlots($startDate, $endDate){

        $bookingRule = $this->bookingRule;

        $weeklySchedule = [];

        if ($bookingRule !== null) {
            $weeklySchedule = json_decode($bookingRule->weekly_schedule, true);
        }

        $weekMap = ['Mon' => 1, 'Tue' => 2, 'Wed' => 3, 'Thu' => 4, 'Fri' => 5, 'Sat' => 6, 'Sun' => 7];

        $loopStartDate = clone $startDate;
        $loopTill = clone $endDate;

        $slots = [];
        while ($loopStartDate <= $loopTill) {
            $day = $weekMap[$loopStartDate->format('D')];
            foreach ($weeklySchedule as $schedule) {
                if (in_array($day, $schedule['days'])) {

                    $from = Carbon::createFromFormat("Y-m-d H:i", $loopStartDate->format('Y-m-d') . " " . $schedule['startTime']);
                    $to = Carbon::createFromFormat("Y-m-d H:i", $loopStartDate->format('Y-m-d') . " " . $schedule['endTime']);

                    $slots[] = [
                        'start' => $from->format('Y-m-d H:i:s'),
                        'end' => $to->format('Y-m-d H:i:s'),
                        'price' => $schedule['price'],
                        'contact' => !empty($contacts[$schedule["contact"]]) ? $contacts[$schedule["contact"]]: "",
                        'title' => $from->format('h:iA') . " - " . $to->format('h:iA'),
                        'only_date' => $from->format('Y-m-d'),
                        'only_day'=> $from->format('D'),
                        'type'=> !empty($schedule['type']) ? $schedule['type']: 'per_team',
                        'sf_id'=> $this->id,
                        'sf_title'=> $this->name. ', '.$this->stadium->name,
                        'booking_status' => 0
                    ];
                }
            }
            $loopStartDate->addDay(1);
        }


        usort($slots, function ($a, $b) {
            return strcmp($a['start'], $b['start']);
        });

        $loopStartDate = Carbon::now();
        $loopTill = clone $endDate;

        $bookings = Booking::with(['club1', 'club2', 'stadiumFacility', 'stadiumFacility.sport'])
            ->where('stadium_id', $this->stadium->id)
            ->where('stadium_facility_id', $this->id)
            ->where(function ($query) {
                $query->orWhere('club1_payment_confirmed', true);
                $query->orWhere('club2_payment_confirmed', true);
            })
            ->where('booking_date', '>=', $loopStartDate->format('Y-m-d'))
            ->where('booking_date', '<', $loopTill->format('Y-m-d'))->get();

        $bookingEvents = [];
        foreach ($bookings as $booking) {
            $bookingEvents[] = [
                'start' => $booking->booking_date . " " . $booking->start_time,
                'end' => $booking->booking_date . " " . $booking->end_time,
                'price' => $booking->club1_fee,
                'contact' => empty($contacts[$booking->contact_person_id]) ? '': $contacts[$booking->contact_person_id],
                'title' => $booking->getTitle(),
                'only_date' => $booking->booking_date,
                'booking_status' => $booking->getBookingStatus(),
                'type' => strtolower($booking->stadiumFacility->sport->name) === 'football' ? 'per_team': 'per_slot'
            ];
        }



        $nonOverlappingSlots = [];
        foreach ($slots as $slot) {
            $overlap = false;
            foreach ($bookings as $booking) {


                $slotStart = Carbon::createFromFormat('Y-m-d H:i:s', $slot['start']);
                $slotEnd = Carbon::createFromFormat('Y-m-d H:i:s', $slot['end']);

                $bookingStart = Carbon::createFromFormat('Y-m-d H:i:s', $booking->booking_date . " " . $booking->start_time);
                $bookingEnd = Carbon::createFromFormat('Y-m-d H:i:s', $booking->booking_date . " " . $booking->end_time);


                $result = $this->datesOverlap($slotStart, $slotEnd, $bookingStart, $bookingEnd);
                if ($result > 0) {
                    $overlap = true;
                }
            }

            if (!$overlap) {
                $nonOverlappingSlots[] = $slot;
            }
        }

        return $nonOverlappingSlots;
    }
    function datesOverlap($start_one, $end_one, $start_two, $end_two)
    {

        if ($start_one <= $end_two && $end_one >= $start_two) { //If the dates overlap
            return min($end_one, $end_two)->diff(max($start_two, $start_one))->days + 1; //return how many days overlap
        }

        return 0; //Return 0 if there is no overlap
    }
}
