<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class S3Helper
{
    public static function upload_image($folder_path, $image){
        return Storage::disk('s3')->put($folder_path, file_get_contents($image));
    }

    public static function get_image_url($image_name, $folder_path){
        return Config::get('aws.s3_content_path') . Config::get('aws.bucket') . '/'. $folder_path . $image_name;
    }
}
