<?php

namespace App;

use App\Helpers\Helper;
use Illuminate\Http\Request;

class Tenant extends BaseModel
{
    public static $validation = [
        'name' => 'required|max:100',
        'email' => 'required|email',
        'sport_id' => 'required',
        'status' => 'required',
    ];

    public const INDEX_URL = 'tenants';

    protected $fillable = [
        'name', 'email', 'sport_id', 'api_token', 'status', 'isDefault',
    ];
    protected $appends = [
        'sportName',
    ];

    public static function filters(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::filters($request);
        return $query;
    }

    public function getSportNameAttribute()
    {
        $sport = Sport::query()->select('name')->find($this->getAttributes()['sport_id']);
        return $sport['name'] ?? 'N/A';
    }

    public function getStatusAttribute($value)
    {
        return Helper::get_status($value);
    }
}
