<?php

namespace App;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class Weather extends BaseModel
{
    protected $table = 'weather';

    protected  $fillable = [
        'id', 'fixture_id', 'temp', 'unit', 'temp_feels_like', 'rain', 'wind', 'weather_type', 'rain_amount' , 'direction', 'cloud', 'relative_humidity', 'gust'
    ];
}
