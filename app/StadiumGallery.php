<?php

namespace App;

use App\Helpers\S3Helper;
use Illuminate\Database\Eloquent\Model;

class StadiumGallery extends Model
{
    protected $table = 'stadium_galleries';
    public const S3_FOLDER_PATH = 'stadiums/';
    protected $fillable = [
        'stadium_id', 'image',
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


}
