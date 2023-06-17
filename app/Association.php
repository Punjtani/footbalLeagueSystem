<?php

namespace App;

use App\Helpers\Helper;
use App\Helpers\S3Helper;
use App\Traits\BelongsToTenant;
use Illuminate\Http\Request;

class Association extends BaseModel
{
    use BelongsToTenant;

    public static array $validation = [
        'name' => 'required|max:100',
        'country' => 'required|max:100',
        'status' => 'required',
        'image' => 'mimes:jpeg,jpg,png,gif|required|max:10000',
    ];

    public const S3_FOLDER_PATH = 'associations/';
    public const INDEX_URL = 'associations';

    protected $fillable = [
        'name', 'status', 'image', 'country',
    ];

    protected $appends = [
        'tenantName',
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

    public function getStatusAttribute($value)
    {
        return Helper::get_status($value);
    }

    public function getCountryAttribute($value)
    {
        return Helper::get_country_name($value);
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
}
