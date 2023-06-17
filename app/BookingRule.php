<?php

namespace App;

use App\Helpers\S3Helper;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Helpers\Helper;

class BookingRule extends BaseModel
{

    public static array $validation = [
        'name' => 'required',
        'booking_window_duration'=> 'required',
    ];

    public const INDEX_URL = 'booking-rules';

    protected $fillable = [
        'name', 'weekly_schedule', 'booking_window_duration'
    ];

    public static function filters(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::filters($request);
        return $query;
    }

    public function stadiumFacilities(){
            return $this->hasMany(StadiumFacility::class);
    }
}
