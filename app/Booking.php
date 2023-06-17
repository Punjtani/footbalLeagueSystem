<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Http\Request;

class Booking extends BaseModel
{

    public static array $validation = [
        'stadium_id' => 'required',
        'stadium_facility_id' => 'required',
        'booking_date' => 'required',
        'start_time' => 'required',
        'end_time' => 'required',
        'tournament_id' => 'required',
        'contact_person_id' => 'required',
        'club1_fee' => 'nullable|numeric',
        'club2_fee' => "nullable|numeric",
        'fee_type' => 'required',
        'slot_fee' => 'nullable|numeric',
        'slot_fee_deposit_paid' => 'nullable|required_with:slot_fee_paid',
        'club1_payment_confirmed' => 'nullable|required_with:club1_fully_paid',
        'club2_payment_confirmed' => 'nullable|required_with:club2_fully_paid',
        'club1_deposit_amount' => 'nullable|numeric|required_with:club1_payment_confirmed',
        'club2_deposit_amount' => 'nullable|numeric|required_with:club2_payment_confirmed',


    ];

    public const INDEX_URL = 'bookings';

    protected $fillable = [
        'stadium_id', 'stadium_facility_id', 'booking_date','booking_type',
        'start_time', 'end_time', 'tournament_id', 'club1_id', 'club2_id',
        'fee_type', 'slot_fee', 'slot_fee_paid', 'slot_deposit', 'slot_fee_deposit_paid',
        'club1_fee', 'club2_fee', 'club1_deposit_amount', 'club2_deposit_amount',
        'club1_payment_confirmed', 'club2_payment_confirmed',
        'club1_payment_proof', 'club2_payment_proof', 'contact_person_id',
        'club1_jersey_color', 'club2_jersey_color', 'club1_fully_paid', 'club2_fully_paid', 'match_referee_required', 'club1_water_boxes', 'club2_water_boxes'
    ];

    public function stadium()
    {
        return $this->belongsTo(Stadium::class);
    }

    public function stadiumFacility()
    {
        return $this->belongsTo(StadiumFacility::class);
    }

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function club1()
    {
        return $this->belongsTo(Club::class, 'club1_id');
    }

    public function club2()
    {
        return $this->belongsTo(Club::class, 'club2_id');
    }

    public function contactPerson()
    {
        return $this->belongsTo(User::class, 'contact_person_id');
    }

    public function match()
    {
        return $this->hasOne(Match::class);
    }

    public function blockBooking()
    {
        return $this->belongsTo(BlockBooking::class);
    }

    public function getTitle()
    {
        if (!empty($this->block_booking_id) && (empty($this->club1) && empty($this->club2))) {
            return $this->blockBooking->name;
        }

        if ($this->fee_type === 'per_team') {
            $title = [];
            if (!empty($this->club1) && $this->club1_payment_confirmed) {
                $title[] = $this->club1->name;
            } else {
                $title[] = "???";
            }

            if (!empty($this->club2) && $this->club2_payment_confirmed) {
                $title[] = $this->club2->name;
            } else {
                $title[] = "???";
            }

            return join(" VS ", $title);
        } else if ($this->fee_type === 'per_slot') {
            $title = [];
            if (!empty($this->club1)) {
                $title[] = $this->club1->name;
            } else {
                $title[] = "???";
            }

            if (!empty($this->club2)) {
                $title[] = $this->club2->name;
            } else {
                $title[] = "???";
            }

            return join(" VS ", $title);
        }


    }

    public function getTitleTeamOne()
    {
        if (!empty($this->block_booking_id) && (empty($this->club1) && empty($this->club2))) {
            return $this->blockBooking->name;
        }

        if ($this->fee_type === 'per_team') {
            if (!empty($this->club1) && $this->club1_payment_confirmed) {
                $title = $this->club1->name;
            } else {
                $title = "???";
            }

            return $title;
        } else if ($this->fee_type === 'per_slot') {
            if (!empty($this->club1)) {
                $title = $this->club1->name;
            } else {
                $title = "???";
            }
            return $title;
        }


    }

    public function getImageTeamOne()
    {

        if (!empty($this->block_booking_id) && (empty($this->club1) && empty($this->club2))) {
            $image = null;
            return $image;
        }
 
        if ($this->fee_type === 'per_team') {
            if (!empty($this->club1) && $this->club1_payment_confirmed) {
                if ((!empty($this->club1->getRawOriginal('image')))) {
                    $image = $this->club1->image;
                } else {
                    // $image = asset('images/empty_logo.png');
                    $image = asset('images/empty_logo.png');
                }
            } else {
                $image = null;
            }

            return $image;
        } else if ($this->fee_type === 'per_slot') {
            if (!empty($this->club1)) {
                if ((!empty($this->club1->getRawOriginal('image')))) {
                    $image = $this->club1->image;
                } else {
                    // $image = asset('images/empty_logo.png');
                    $image = asset('images/empty_logo.png');
                }
            } else {
                $image = null;
            }

            return $image;
        }


    }
    public function getImageTeamOneForPoster()
    {

        if (!empty($this->block_booking_id) && (empty($this->club1) && empty($this->club2))) {
            $image = null;
            return $image;
        }

        if ($this->fee_type === 'per_team') {
            if (!empty($this->club1) && $this->club1_payment_confirmed) {
                if ((!empty($this->club1->getRawOriginal('image')))) {
                    $image = $this->club1->image;
                } else {
                    // $image = asset('images/empty_logo.png');
                    $image = public_path('images/empty_logo.png');
                }
            } else {
                $image = null;
            }

            return $image;
        } else if ($this->fee_type === 'per_slot') {
            if (!empty($this->club1)) {
                if ((!empty($this->club1->getRawOriginal('image')))) {
                    $image = $this->club1->image;
                } else {
                    // $image = asset('images/empty_logo.png');
                    $image = public_path('images/empty_logo.png');
                }
            } else {
                $image = null;
            }

            return $image;
        }


    }

    public function getTitleTeamTwo()
    {
        if (!empty($this->block_booking_id) && (empty($this->club1) && empty($this->club2))) {
            return $this->blockBooking->name;
        }

        if ($this->fee_type === 'per_team') {
            if (!empty($this->club2) && $this->club2_payment_confirmed) {
                $title = $this->club2->name;
            } else {
                $title = "???";
            }

            return $title;
        } else if ($this->fee_type === 'per_slot') {
            if (!empty($this->club2)) {
                $title = $this->club2->name;
            } else {
                $title = "???";
            }
            return $title;

        }


    }

    public function getImageTeamTwo()
    {
        if (!empty($this->block_booking_id) && (empty($this->club1) && empty($this->club2))) {
            $image = null;
            return $image;
        }

        if ($this->fee_type === 'per_team') {
            if (!empty($this->club2) && $this->club2_payment_confirmed) {
                if ((!empty($this->club2->getRawOriginal('image')))) {
                    $image = $this->club2->image;
                } else {
                    // $image = asset('images/empty_logo.png');
                    $image = asset('images/empty_logo.png');
                }
            } else {
                $image = null;
            }

            return $image;
        } else if ($this->fee_type === 'per_slot') {
            if (!empty($this->club2)) {
                if ((!empty($this->club2->getRawOriginal('image')))) {
                    $image = $this->club2->image;
                } else {
                    // $image = asset('images/empty_logo.png');
                    $image = asset('images/empty_logo.png');
                }
            } else {
                $image = null;
            }

            return $image;
        }


    }
    public function getImageTeamTwoForPoster()
    {
        if (!empty($this->block_booking_id) && (empty($this->club1) && empty($this->club2))) {
            $image = null;
            return $image;
        }

        if ($this->fee_type === 'per_team') {
            if (!empty($this->club2) && $this->club2_payment_confirmed) {
                if ((!empty($this->club2->getRawOriginal('image')))) {
                    $image = $this->club2->image;
                } else {
                    // $image = asset('images/empty_logo.png');
                    $image = public_path('images/empty_logo.png');
                }
            } else {
                $image = null;
            }

            return $image;
        } else if ($this->fee_type === 'per_slot') {
            if (!empty($this->club2)) {
                if ((!empty($this->club2->getRawOriginal('image')))) {
                    $image = $this->club2->image;
                } else {
                    // $image = asset('images/empty_logo.png');
                    $image = public_path('images/empty_logo.png');
                }
            } else {
                $image = null;
            }

            return $image;
        }


    }

    public function getStartDateTimeAttribute()
    {

        $startTime = $this->start_time;
        if (strlen($startTime) <= 5) {
            $startTime .= ':00';
        }
        return Carbon::createFromFormat('Y-m-d H:i:s', "{$this->booking_date} {$startTime}");
    }

    public function getEndDateTimeAttribute()
    {
        $endTime = $this->end_time;
        if (strlen($endTime) <= 5) {
            $endTime .= ':00';
        }
        return Carbon::createFromFormat('Y-m-d H:i:s', "{$this->booking_date} {$endTime}");
    }

    public function collectedPayment()
    {
        $collected = 0;
        if (!empty($this->club1)) {
            if ($this->club1_fully_paid) {
                $collected += $this->club1_fee;
            } else if ($this->club1_payment_confirmed) {
                $collected += $this->club1_deposit_amount;
            } else {
                $collected += 0;
            }
        }

        if (!empty($this->club2)) {
            if ($this->club2_fully_paid) {
                $collected += $this->club2_fee;
            } else if ($this->club2_payment_confirmed) {
                $collected += $this->club2_deposit_amount;
            } else {
                $collected += 0;
            }
        }

        return $collected;
    }

    public function pendingPayment()
    {
        $uncollected = 0;
        if (!empty($this->club1)) {
            if ($this->club1_fully_paid) {
                $uncollected = 0;
            } else if ($this->club1_payment_confirmed) {
                $uncollected = $this->club1_fee - $this->club1_deposit_amount;
            } else {
                $uncollected = $this->club1_fee;
            }
        }

        if (!empty($this->club2)) {
            if ($this->club2_fully_paid) {
                $uncollected += 0;
            } else if ($this->club2_payment_confirmed) {
                $uncollected += ($this->club2_fee - $this->club2_deposit_amount);
            } else {
                $uncollected += $this->club2_fee;
            }
        }

        return $uncollected;
    }

    public function bookingStatusX()
    {

        if ($this->fee_type === 'per_slot') {

            if ($this->start_date_time < Carbon::now() && ($this->slot_fee_paid === false)) {
                return "Uncollected";
            }
            if ($this->slot_fee_paid) {
                return "Collected";
            }

            if ($this->slot_fee_deposit_paid) {
                return 'Match';
            }

            return "Unknown Status";
        }
        if ($this->club1 !== null && $this->club2 !== null) {

            if ($this->club1_payment_confirmed === false && $this->club2_payment_confirmed === false) {
                return "No Deposit";
            }


            if ($this->start_date_time < Carbon::now() && ($this->club1_fully_paid === false || $this->club2_fully_paid === false)) {
                return "Uncollected";
            }

            if ($this->club1_fully_paid && $this->club2_fully_paid) {
                return "Collected";
            }
            if ($this->club1_payment_confirmed && $this->club2_payment_confirmed) {
                return "Match";
            } else if ($this->club1_payment_confirmed || $this->club2_payment_confirmed) {
                return "Looking For Opponent";
            }
        }

        if ($this->club1 === null || $this->club2 === null) {
            $club = $this->club1 !== null ? "club1" : "club2";
            if ($this->{$club . "_fully_paid"} || $this->{$club . "_payment_confirmed"}) {
                return "Looking For Opponent";
            }
            return "No Deposit";
        }
    }

    public function getBookingStatus()
    {
        if (!empty($this->block_booking_id)) {
            return 2;
        }
        $status = $this->bookingStatusX();
        switch ($status) {
            case 'Uncollected':
            case 'No Deposit':
                return 0;

            case 'Match':
            case 'Collected':
                return 2;
            case 'Looking For Opponent':
                return 1;
        }
    }

    public function getBookingStatusForFrontEnd()
    {
        // if a booking is in past just show it in red booked
        if ($this->start_date_time < Carbon::now()) {
            return 3;
        }

        if (!empty($this->block_booking_id)) {
            return 2;
        }
        $status = $this->bookingStatusX();
        switch ($status) {
            case 'Uncollected':
            case 'No Deposit':
                return 0;

            case 'Match':
            case 'Collected':
                return 2;
            case 'Looking For Opponent':
                return 1;
        }
    }


    public static function filters(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = self::query();
        $query->with(['stadium', 'stadiumFacility', 'club1', 'club2', 'tournament']);

        if (!empty($request->search_filter)) {
            $query->where(function ($query) use ($request) {
                $query->orWhereHas('club1', function ($query) use ($request) {
                    $query->where('name', 'ilike', '%' . $request->search_filter . '%');
                });
                $query->orWhereHas('club2', function ($query) use ($request) {
                    $query->where('name', 'ilike', '%' . $request->search_filter . '%');
                });

                $query->orWhereHas('stadium', function ($query) use ($request) {
                    $query->where('name', 'ilike', '%' . $request->search_filter . '%');
                });

                $query->orWhereHas('tournament', function ($query) use ($request) {
                    $query->where('name', 'ilike', '%' . $request->search_filter . '%');
                });
            });
        }


        $query->when($request->startDate, function ($query, $startDate) {
            $query->where('booking_date', '>=', $startDate);
        });

        $query->when($request->endDate, function ($query, $endDate) {
            $query->where('booking_date', '<=', $endDate);
        });

        $query->when($request->stadium_id, function ($query, $stadiumId) {
            $query->where('stadium_id', $stadiumId);
        });

        $query->when($request->contact_person_id, function ($query, $contact_person_id) {
            $query->where('contact_person_id', $contact_person_id);
        });

        $query->when($request->stadium_facility_id, function ($query, $stadium_facility_id) {
            $query->where('stadium_facility_id', $stadium_facility_id);
        });

        $query->when($request->sport_id, function ($query, $sport_id) {
            $query->whereHas('stadiumFacility', function ($query) use ($sport_id) {
                $query->where('sport_id', $sport_id);
            });
        });


        $query->when($request->status, function ($query, $status) {
            switch ($status) {
                case 'full-collected':
                    $query->where(function ($query) {
                        $query->orWhere(function ($query) {
                            $query->where('fee_type', 'per_team');
                            $query->whereNotNull('club1_id');
                            $query->whereNotNull('club2_id');
                            $query->where('club1_fully_paid', true);
                            $query->where('club2_fully_paid', true);
                        });

                        $query->orWhere(function ($query) {
                            $query->where('fee_type', 'per_slot');
                            $query->where('slot_fee_paid', true);
                        });

                    });
                    break;
                case 'match':

                    $query->where(function ($query) {
                        $query->orWhere(function ($query) {
                            $query->where('fee_type', 'per_team');
                            $query->whereNotNull('club1_id');
                            $query->whereNotNull('club2_id');
                            $query->where('club1_payment_confirmed', true);
                            $query->where('club2_payment_confirmed', true);
                            $query->where('club1_fully_paid', false);
                            $query->where('club2_fully_paid', false);
                            $query->whereRaw('CONCAT("booking_date",\' \', "start_time")::TIMESTAMP > \'' . Carbon::now()->format('Y-m-d H:i:s') . '\'');

                        });

                        $query->orWhere(function ($query) {
                            $query->where('fee_type', 'per_slot');
                            $query->where('slot_fee_deposit_paid', true);
                            $query->where('slot_fee_paid', false);
                            $query->whereRaw('CONCAT("booking_date",\' \', "start_time")::TIMESTAMP > \'' . Carbon::now()->format('Y-m-d H:i:s') . '\'');
                        });

                    });

                    break;
                case 'looking-oppo':

                    $query->where(function ($query) {
                        $query->orWhere(function ($query) {
                            $query->where('fee_type', 'per_team');
                            $query->where(function ($query) {
                                $query->orWhere(function ($query) {
                                    $query->whereNotNull('club1_id');
                                    $query->whereNotNull('club2_id');
                                    $query->whereRaw('xor("club1_payment_confirmed","club2_payment_confirmed")');
                                });

                                $query->orWhere(function ($query) {
                                    $query->whereRaw('xor(("club1_id" IS NULL), ("club2_id" IS NULL))');
                                    $query->where(function ($query) {
                                        $query->orWhere(function ($query) {
                                            $query->orWhere('club1_payment_confirmed', true);
                                            $query->orWhere('club2_payment_confirmed', true);
                                        });
                                        $query->orWhere(function ($query) {
                                            $query->orWhere('club1_fully_paid', true);
                                            $query->orWhere('club2_fully_paid', true);
                                        });
                                    });

                                });
                            });
                        });

                        $query->orWhere(function ($query) {
                            $query->where('fee_type', 'per_slot');
                            $query->whereRaw('1<>1');
                        });

                    });


                    break;
                case 'uncollected':

                    $query->where(function ($query) {
                        $query->orWhere(function ($query) {
                            $query->where('fee_type', 'per_team');
                            $query->whereRaw('CONCAT("booking_date",\' \', "start_time")::TIMESTAMP < \'' . Carbon::now()->format('Y-m-d H:i:s') . '\'');
                            $query->where(function ($query) {
                                $query->orWhere('club1_fully_paid', false);
                                $query->orWhere('club2_fully_paid', false);
                            });
                        });

                        $query->orWhere(function ($query) {
                            $query->where('fee_type', 'per_slot');
                            $query->whereRaw('CONCAT("booking_date",\' \', "start_time")::TIMESTAMP < \'' . Carbon::now()->format('Y-m-d H:i:s') . '\'');
                            $query->where('slot_fee_paid', false);
                        });


                    });


//                    $query->where(function($query){
//                        $query->where('club1_fully_paid',false);
//                        $query->where('club2_fully_paid',false);
//                        $query->where('club1_payment_confirmed',false);
//                        $query->where('club2_payment_confirmed',false);
//                    });
                    break;
            }
        });


        return $query;
    }

    public static function unpaidBooking()
    {
        $query = self::query();
        $query->whereRaw('CONCAT("booking_date",\' \', "start_time")::TIMESTAMP < \'' . Carbon::now()->format('Y-m-d H:i:s') . '\'');
        $query->where(function ($query) {
            $query->orWhere(function ($query) {
                $query->where('fee_type', 'per_team');
                $query->where(function ($query) {
                    $query->orWhere('club1_fully_paid', false);
                    $query->orWhere('club2_fully_paid', false);
                });
            });
            $query->orWhere(function ($query) {
                $query->where('fee_type', 'per_slot');
                $query->where('slot_fee_paid', false);
            });

        });
        return $query;
    }

}
