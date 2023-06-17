<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Config;
use Intervention\Image\Facades\Image;

class ImageHelper
{
    public static function resize($image){
        $img = Image::make(file_get_contents($image));
        return $img->resize(Config::get('image.imageWidth'), Config::get('image.imageHeight'))->stream();
    }
}
