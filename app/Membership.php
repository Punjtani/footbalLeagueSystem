<?php

namespace App;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class Membership extends BaseModel
{

    protected  $fillable = [
        'id', 'club_id', 'membership_level_id', 'status', 'expires_at'
    ];

    protected $dates = [

        'created_at', 'updated_at', 'expires_at'
    ];


    public function club(){
        return $this->belongsTo(Club::class);
    }

    public function membershipLevel(){
        return $this->belongsTo(MembershipLevel::class);
    }

    public function bookingCount(){
        $membership = $this;
        $query = Booking::where(function($query) use ($membership){
            $query->orWhere('club1_id', $membership->club_id);
            $query->orWhere('club2_id', $membership->club_id);
        });
        $query->whereRaw('CONCAT("booking_date",\' \', "start_time")::TIMESTAMP >= \''.$membership->created_at->format('Y-m-d H:i:s').'\'');
        $query->whereRaw('CONCAT("booking_date",\' \', "start_time")::TIMESTAMP <= \''.$membership->expires_at->format('Y-m-d H:i:s').'\'');

        return $query->count();
    }
}
