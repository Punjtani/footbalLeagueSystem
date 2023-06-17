<?php

namespace App;

use App\Helpers\S3Helper;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Helpers\Helper;

class BlockBooking extends BaseModel
{

    public static array $validation = [
        'name' => 'required',
        'booked_by' => 'required',
        'sport_id' => 'required',
        'tournament_id' => 'required',
        'is_payment_collected' => 'sometimes',
        'bookings'=> 'required',
        'contact_person_id'=> 'required'
    ];

    public const INDEX_URL = 'block-booking';

    protected $fillable = [
        'name',
        'booked_by',
        'sport_id',
        'tournament_id',
        'status',
        'is_payment_collected',
        'contact_person_id'
    ];

    public static function filters(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::filters($request);
        return $query;
    }

    public function sport(){
            return $this->belongsTo(Sport::class);
    }

    public function tournament(){
        return $this->belongsTo(Tournament::class);
    }

    public function bookings(){
        return $this->hasMany(Booking::class);
    }
}
