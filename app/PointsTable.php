<?php

namespace App;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class PointsTable extends BaseModel
{
    protected $table = 'points_table_data';

    protected $fillable = [
        'id', 'team_id', 'tournament_id', 'team_points', 'created_at', 'updated_at'
    ];
}
