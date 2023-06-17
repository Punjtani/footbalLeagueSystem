<?php

namespace App;

use App\Helpers\S3Helper;
use App\Traits\BelongsToTenant;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends BaseModel
{
    use BelongsToTenant;
    use SoftDeletes;

    public const INDEX_URL = 'subscriptions';
    protected $table = 'subscriptions';
    
    protected $fillable = [
        'user_id', 'subscription_id', 'status', 'email', 'is_first_of_month'
    ];

    protected $dates = ['deleted_at'];
    
    public static function filters(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::filters($request);
        return $query;
    }

}
