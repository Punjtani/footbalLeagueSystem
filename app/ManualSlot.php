<?php

namespace App;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class ManualSlot extends BaseModel
{
    protected $table = 'manual_slots';

    protected  $fillable = [
        'facility_id','slot_date', 'slot_time_start', 'slot_time_end', 'price', 'contacts', 'type'
    ];
}
