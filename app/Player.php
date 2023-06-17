<?php

namespace App;

use App\Helpers\Helper;
use App\Helpers\S3Helper;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class Player extends BaseModel
{
    use BelongsToTenant;

    public static array $validation = [

    ];

    public const S3_FOLDER_PATH = 'players/';
    public const INDEX_URL = 'players';

    protected $fillable = [
        'name', 'status', 'image', 'country', 'dob', 'jersey', 'playing_position', 'facebook', 'twitter', 'instagram', 'youtube', 'height'
    ];
    protected $appends = [
        'socials',
    ];
    protected $casts = [
        'latitude' => 'string',
        'longitude' => 'string',
        'dob' => 'date',
    ];

    public static function filters(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::filters($request);
        if ($request->has('club_id--filter') && $request->input('club_id--filter') !== NULL) {
            $query->leftJoin('club_player', 'players.id', '=', 'club_player.player_id');
            $query->where('club_player.club_id', $request->input('club_id--filter'));
            $query->where('club_player.left_on', NULL);
        }
        if ($request->has('team_id--filter') && $request->input('team_id--filter') !== NULL) {
            $query->leftJoin('player_team', 'players.id', '=', 'player_team.player_id');
            $query->leftJoin('teams', 'player_team.team_id', '=', 'teams.id');
            $query->where('teams.team_group', $request->input('team_id--filter'));
        }
        if ($request->has('socials--filter') && $request->input('socials--filter') !== NULL) {
            $query->whereNotNull($request->input('socials--filter'));
        }
        return $query;
    }

    public function getNameAttribute($value)
    {
        return Helper::get_default_lang($value);
    }

    public function getSocialsAttribute()
    {
        $html = '';
        $line = false;
        $html .= '<div class="text-truncate">';
        if ($this->facebook !== null && $this->facebook !== '') {
            $line = true;
            $html .= '<span class="btn btn-icon btn-flat-primary"><i class="feather icon-facebook"></i></span>';
        }
        if ($this->twitter !== null && $this->twitter !== '') {
            $line = true;
            $html .= '<span class="btn btn-icon btn-flat-primary"><i class="feather icon-twitter"></i></span>';
        }
        if ($this->instagram !== null && $this->instagram !== '') {
            $line = true;
            $html .= '<span class="btn btn-icon btn-flat-primary"><i class="feather icon-instagram"></i></span>';
        }
        if ($this->youtube !== null && $this->youtube !== '') {
            $line = true;
            $html .= '<span class="btn btn-icon btn-flat-primary"><i class="lab la-youtube"></i></span>';
        }
        if (! $line) {
            $html = '<span class="btn btn-sm btn-flat-primary"><i class="feather icon-x"></i></span>';
        }
        $html .= '</div>';
        return $html;
    }

    public function getCountryAttribute($value)
    {
        return Helper::get_country_name($value);
    }

    public function getStatusAttribute($value)
    {
        return Helper::get_status($value);
    }

    public function getDobAttribute($value)
    {
        return Carbon::make($value)->toDateString();
    }

    /**
     * @param $value
     */
    public function setImageAttribute($value){
        $imageName = '';
        if ($value !== '') {
            $imageName = time() . '.' . $value->getClientOriginalExtension();
            $filePath = self::S3_FOLDER_PATH . $imageName;
            S3Helper::upload_image($filePath, $value);
        }
        $this->attributes['image'] = $imageName;
    }

    /**
     * @param $value
     * @return string
     */
    public function getImageAttribute($value){
        return S3Helper::get_image_url($value, self::S3_FOLDER_PATH);
    }
    public function club()
    {
        return $this->belongsToMany(Club::class, 'club_player', 'player_id', 'club_id')->withPivot(['joined_on', 'left_on'])->withTimestamps()->where('left_on',NULL);
    }
    public function teams(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'player_team', 'player_id', 'team_id')->withPivot(['joined_on'])->withTimestamps();
    }
    public function seasons()
    {
        return $this->belongsToMany(Season::class, 'player_season_team', 'player_id', 'season_id')->withTimestamps();
    }
}
