<?php

namespace App;

use App\Helpers\Helper;
use App\Traits\BelongsToTenant;
use Cviebrock\EloquentTaggable\Taggable;
use Illuminate\Http\Request;

class News extends BaseModel
{
    use BelongsToTenant, Taggable;
    protected $table = 'news';


    public static array $validation = [
//        'image' => 'mimes:jpeg,jpg,png,gif|required|max:10000',
    ];

    public const S3_FOLDER_PATH = 'news/';
    public const INDEX_URL = 'news';

    protected $fillable = [
        'author', 'title', 'description', 'content', 'image', 'url', 'source', 'published_at', 'status'
    ];

    public static function filters(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::filters($request);

        if ($request->has('news_tag--filter') && $request->input('news_tag--filter') !== NULL) {
            $query->leftJoin('taggable_taggables', 'taggable_taggables.taggable_id', '=', 'news.id');
            $query->leftJoin('taggable_tags', 'taggable_tags.tag_id', '=', 'taggable_taggables.tag_id');
            $query->where('taggable_tags.name', 'ILIKE', $request->input('news_tag--filter'));
        }

        return $query;
    }

    public function getTitleAttribute($value)
    {
        return Helper::get_default_lang($value);
    }

    public function getDescriptionAttribute($value)
    {
        return Helper::get_default_lang($value);
    }

    public function getContentAttribute($value)
    {
        return Helper::get_default_lang($value);
    }
}
