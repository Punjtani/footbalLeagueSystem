<?php

namespace App;

use App\Helpers\Helper;
use App\Helpers\S3Helper;
use Illuminate\Http\Request;

class Sponsor extends BaseModel
{
    public static $validation = [
        'name' => 'required',
        'image' => 'mimes:jpeg,jpg,png,gif|required|max:10000',
    ];

    public const S3_FOLDER_PATH = 'sponsors/';
    public const INDEX_URL = 'sponsors';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'image', 'status'
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
