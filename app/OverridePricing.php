<?php

namespace App;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class OverridePricing extends BaseModel
{
    protected $table = 'override_pricings';

    protected  $fillable = [
        'facility_id','slot_date', 'slot_time_start', 'slot_time_end', 'overrided_price'
    ];
}
