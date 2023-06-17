<?php

namespace App;

use App\Helpers\Helper;
use App\Traits\BelongsToTenant;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class SeasonTemplate extends BaseModel
{
    use BelongsToTenant;

    public const S3_FOLDER_PATH = 'season-templates/';
    public const INDEX_URL = 'season-templates';

    protected $table = 'season_templates';

    protected $fillable = [
        'name', 'type', 'number_of_teams', 'number_of_stages', 'status'
    ];

    protected $appends = [

    ];

    public static function filters(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::filters($request);
        return $query;
    }

    public function getNameAttribute($value)
    {
        return $value ?? '';
    }

    public function getStatusAttribute($value)
    {
        return Helper::get_status($value);
    }

    public function getTypeAttribute ($value)
    {
        $template_types = Config::get('custom.template_types');
        return $template_types[$value] ?? 'No Template Selected';
    }

    public function getConfigurationAttribute ($value)
    {
        try {
            return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {

        }
        return NULL;
    }
}
