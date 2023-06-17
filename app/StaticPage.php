<?php

namespace App;

use App\Traits\BelongsToTenant;

class StaticPage extends BaseModel
{
    use BelongsToTenant;

    public const S3_FOLDER_PATH = 'static-pages/';
    public const INDEX_URL = 'static-pages';

    protected $fillable = [
        'page_name', 'content'
    ];

    public function getContentAttribute($value)
    {
        if ($this->page_name === 'faq') {
            try {
                return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
            }
        }
        return $value;
    }
}
