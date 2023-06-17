<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Squad extends Model
{

    protected $table = 'squads';
    protected $fillable = [
        'user_id', 'name'
    ];
}
