<?php

namespace App;
use App\Helpers\Helper;
use App\Helpers\S3Helper;
use Illuminate\Http\Request;
use Symfony\Component\Console\Input\Input;

class Fixture extends BaseModel
{

    public const S3_FOLDER_PATH = 'fixtures/';
    public const INDEX_URL = 'fixtures';

    protected $fillable = [
        'match_day', 'season_id', 'team_a_id', 'team_b_id', 'stage_id', 'stadium_id', 'referee_id', 'scheduled_date','match_status', 'weather'
    ];

//    protected $appends = [
//        'tenantName',
//    ];

    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }

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

    /**
     * @param $value
     */
    public function setOccurrenceAttribute($value){
        if ($value === '2') {
            $this->attributes['occurrence'] = request('year_occurrence');
        } else {
            $this->attributes['occurrence'] = $value;
        }
    }

    public function setAssociationAttribute($value)
    {
        $association = Association::query()->findOrFail($value);
        $this->attributes['association_id'] = $association->getAttributes()['id'];
    }
}
