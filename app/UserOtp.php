<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserOtp extends Model
{

    protected $table = 'user_otps';
    protected $fillable = [
        'user_id', 'otp', 'status', 'type','created_at'
    ];
}
