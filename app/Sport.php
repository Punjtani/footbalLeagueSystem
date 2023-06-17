<?php

namespace App;

use App\Helpers\Helper;
use Illuminate\Http\Request;

class Sport extends BaseModel
{
    public static $validation = [
        'name' => 'required',
        'rules' => 'required',
        'scoring' => 'required',
        'stats' => 'required',
        'roles' => 'required',
        'status' => 'required',
    ];

    public const S3_FOLDER_PATH = 'sports/';
    public const INDEX_URL = 'sports';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'status', 'rules', 'stats', 'scoring', 'roles', 'groups',
    ];

    public static function filters(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::filters($request);
        return $query;
    }

    public function setRulesAttribute($rules)
    {
        $this->attributes['rules'] = is_array($rules) ? json_encode($rules) : $rules;
    }

    public function setScoringAttribute($scoring)
    {
        $this->attributes['scoring'] = is_array($scoring) ? json_encode($scoring) : $scoring;
    }

    public function setRolesAttribute($roles)
    {
        $this->attributes['roles'] = is_array($roles) ? json_encode($roles) : $roles;
    }

    public function setStatsAttribute($stats)
    {
        $this->attributes['stats'] = is_array($stats) ? json_encode($stats) : $stats;
    }

    public function getStatusAttribute($value)
    {
        return Helper::get_status($value);
    }

    public function stadium_facilities(){
        return $this->hasMany(StadiumFacility::class, 'sport_id');
    }

    public function stadiums(){
        $stadiums = [];
        foreach($this->stadium_facilities as $facility){
            if($facility->status === 1){
                $stadiums[$facility->stadium->id] = $facility->stadium;
            }
        }

        return $stadiums;
    }
}
