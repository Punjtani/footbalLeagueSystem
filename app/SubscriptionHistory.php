<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionHistory extends Model
{
    use SoftDeletes;
    protected $table = 'subscription_histories';
    protected $fillable = [
        'user_id', 'subscription_date', 'amount'
    ];
    
    protected $dates = ['deleted_at'];
}
