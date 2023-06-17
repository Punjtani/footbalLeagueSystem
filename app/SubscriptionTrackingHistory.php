<?php

namespace App;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class SubscriptionTrackingHistory extends Model
{
    use BelongsToTenant;

    protected $table = 'subscription_tracking_histories';

    protected $fillable = [
        'user_id', 'subscription_id', 'status', 'email', 'username'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->select('id', 'email');
    }
}
