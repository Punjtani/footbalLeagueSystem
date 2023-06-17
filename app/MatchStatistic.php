<?php

namespace App;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class MatchStatistic extends BaseModel
{
    protected $fillable = [
        'id', 'match_id', 'team_id', 'stat_key', 'player_id', 'minute_of_action', 'stat_value', 'is_own_goal', 'created_at', 'updated_at'
    ];
}
