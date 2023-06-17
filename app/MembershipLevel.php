<?php

namespace App;

use App\Helpers\S3Helper;
use App\Helpers\Helper;

class MembershipLevel extends BaseModel
{
    protected $table = 'membership_levels';

    public const S3_FOLDER_PATH = 'membership-levels/';
    public const INDEX_URL = 'membership-levels';

    public const DISCOUNT_TYPE_FIXED = 'Fixed';
    public const DISCOUNT_TYPE_PERCENTAGE = 'Percentage';

    public static $validation = [
        'discount_type' => 'required',
        'discount_value' => 'required|numeric',
        'status' => 'required',
        'no_of_bookings'=> 'required',
    ];

    protected  $fillable = [
      'name', 'image', 'discount_type', 'discount_value', 'no_of_bookings', 'status'
    ];

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

    public function getNameAttribute($value)
    {
        return Helper::get_default_lang($value);
    }

    public function clubs(){
       return $this->hasMany(Club::class);
    }

}
