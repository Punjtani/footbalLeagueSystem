<?php

namespace App;

use App\Helpers\S3Helper;
use App\Traits\BelongsToTenant;
use Illuminate\Http\Request;
use App\Helpers\Helper;

class Team extends BaseModel
{
    use BelongsToTenant;

    public static array $validation = [
//        'image' => 'mimes:jpeg,jpg,png,gif|required|max:10000',
    ];

    public const S3_FOLDER_PATH = 'team/';
    public const INDEX_URL = 'teams';

    protected $fillable = [
        'name', 'description', 'status', 'club_id',  'team_group', 'is_default_team',
    ];

    public static function filters(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::filters($request);
        return $query;
    }

    public function getNameAttribute($value)
    {
        return Helper::get_default_lang($value);
    }

    public function getDescriptionAttribute($value)
    {
        return Helper::get_default_lang($value);
    }

    public function getStatusAttribute($value)
    {
        return Helper::get_status($value);
    }

    public function getImageAttribute()
    {
        $club = Club::query()->select('image')->find($this->club_id);
        if ($club === NULL) {
            return '';
        }
        return $club->image;
    }

    public function getClubNameAttribute()
    {
        $club = Club::query()->select('name')->find($this->club_id);
        if ($club === NULL) {
            return '';
        }
        return $club->name;
    }
    public function getStadiumIdAttribute()
    {
        $club = Club::query()->select('stadium_id')->find($this->club_id);
        if ($club === NULL) {
            return '';
        }
        return $club->stadium_id;
    }
    public function getFacebookAttribute()
    {
        $club = Club::query()->select('facebook')->find($this->club_id);
        if ($club === NULL) {
            return '';
        }
        return $club->facebook;
    }
    public function getInstagramAttribute()
    {
        $club = Club::query()->select('instagram')->find($this->club_id);
        if ($club === NULL) {
            return '';
        }
        return $club->instagram;
    }
    public function getTwitterAttribute()
    {
        $club = Club::query()->select('twitter')->find($this->club_id);
        if ($club === NULL) {
            return '';
        }
        return $club->twitter;
    }
    public function getYoutubeAttribute()
    {
        $club = Club::query()->select('youtube')->find($this->club_id);
        if ($club === NULL) {
            return '';
        }
        return $club->youtube;
    }

    public function getFoundingDateAttribute()
    {
        $club = Club::query()->select('founding_date')->find($this->club_id);
        if ($club === NULL) {
            return '';
        }
        return $club->founding_date;
    }

    public function staff(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Staff::class);
    }

    public function club(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->BelongsTo(Club::class);

    }
    public function seasons(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Season::class, 'player_season_team', 'team_id', 'season_id')->withTimestamps();
    }
    public function players(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'player_team', 'team_id', 'player_id')->withPivot('joined_on');
    }
    public function captains(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'captain_team', 'team_id', 'player_id');
    }
}
