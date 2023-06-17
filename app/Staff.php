<?php

namespace App;

use App\Helpers\Helper;
use App\Helpers\S3Helper;
use App\Traits\BelongsToTenant;
use Illuminate\Http\Request;

class Staff extends BaseModel
{
    use BelongsToTenant;

    public static array $validation = [
        'name' => 'required|max:100',
        'country' => 'required',
        'status' => 'required',
        'type' => 'required',
        'image' => 'mimes:jpeg,jpg,png,gif|required|max:10000',
    ];

    public const S3_FOLDER_PATH = 'staff/';
    public const INDEX_URL = 'staff';

    protected $fillable = [
        'name', 'status', 'image', 'country', 'type', 'team_id',
    ];
    protected $casts = [
        'latitude' => 'string',
        'longitude' => 'string',
    ];

    public static function filters(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::filters($request);
        if ($request->has('team_group--filter') && $request->input('team_group--filter') !== NULL) {
            $query->leftJoin('teams', 'staff.team_id', '=', 'teams.id');
            $query->where('teams.team_group', $request->input('team_group--filter'));
        }
        if ($request->has('club_id--filter') && $request->input('club_id--filter') !== NULL) {
            if ($request->has('team_group--filter') && $request->input('team_group--filter') === NULL) {
                $query->leftJoin('teams', 'staff.team_id', '=', 'teams.id');
            }
            $query->leftJoin('clubs', 'clubs.id', '=', 'teams.club_id');
            $query->where('clubs.id', $request->input('club_id--filter'));
        }
        return $query;
    }

    public function getNameAttribute($value)
    {
        return Helper::get_default_lang($value);
    }

    public function getCountryAttribute($value)
    {
        return Helper::get_country_name($value);
    }

    public function getStatusAttribute($value)
    {
        return Helper::get_status($value);
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

    public function team(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
