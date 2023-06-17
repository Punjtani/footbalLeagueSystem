<?php

namespace App;

use Illuminate\Http\Request;

class Match extends BaseModel
{

    protected $table = "matches";
    public static array $validation = [
    ];

    public const INDEX_URL = "tournaments";

    protected $fillable = [
        'booking_id','team_1_score', 'team_2_score', 'match_status', 'match_result', 'youtube_link'
    ];

    public static function filters(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        return parent::filters($request);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
