<?php

namespace App;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'name', 'season_id', 'stage_number', 'type', 'configuration',
    ];

    public static array $validation = [
        'name' => 'required|max:100',
        'country' => 'required',
        'status' => 'required',
        'type' => 'required',
        'image' => 'mimes:jpeg,jpg,png,gif|required|max:10000',
    ];

    public function fixtures()
    {
        return $this->hasMany(Fixture::class);
    }

    public function season(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Season::class);
    }
}
