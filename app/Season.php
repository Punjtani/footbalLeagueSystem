<?php

namespace App;

use App\Helpers\Helper;
use App\Helpers\S3Helper;
use App\Traits\BelongsToTenant;
use Illuminate\Http\Request;

class Season extends BaseModel
{
    use BelongsToTenant;

    public const S3_FOLDER_PATH = 'seasons/';
    public const INDEX_URL = 'seasons';
    public const STAGE_TYPE_ROUND_ROBIN = 1, STAGE_TYPE_KNOCKOUT = 2;

    protected $fillable = [
        'name', 'status', 'tournament_id', 'season_template_id', 'image'
    ];

    public static function filters(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::filters($request);
        return $query;
    }

    public function getStatusAttribute($value)
    {
        return Helper::get_status($value);
    }

    public function tournament(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function getNameAttribute($value)
    {
        return Helper::get_default_lang($value);
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

    public function stages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Stage::class);
    }

    public function teams(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'player_season_team', 'season_id', 'team_id')->withTimestamps();
    }

    public function players(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'player_season_team', 'season_id', 'player_id')->withTimestamps();
    }
}
