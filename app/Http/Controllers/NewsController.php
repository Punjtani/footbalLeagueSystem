<?php

namespace App\Http\Controllers;

use App\Association;
use App\Club;
use App\Helpers\Helper;
use App\Helpers\HtmlTemplatesHelper;
use App\News;
use App\Player;
use App\Tournament;
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;
use Exception;
use Illuminate\Http\Request;

class NewsController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        try {
            if (request()->ajax()) {
                return datatables(News::filters(request())->select('news.*'))->addColumn('actions', static function($data){
                    return HtmlTemplatesHelper::get_action_dropdown($data);
                })->addColumn('tags', static function($data) {
                    return $data->tagList ?? '';
                })->rawColumns(['actions', 'status'])->make(true);
            }
        } catch (Exception $ex){
        }
        return view('pages.news.list', ['page_name' => 'news', 'add_url' => route("news.create"), 'advance_filters' => $this->advance_filters(), 'breadcrumbs' => Breadcrumbs::generate('news')]);
    }

    private function advance_filters(): array
    {
        $temp_tags = Helper::get_all_content();
        $tags = array();
        foreach ($temp_tags as $key => $list) {
            $tag_item = array();
            foreach ($list->toArray() as $item) {
                $tag_item[$item['name']] = $item['name'];
            }
            $tags[$key] = $tag_item;
        }
        return array(
            'Description' => array('type' => 'text', 'sub_type' => 'like', 'placeholder' => 'Search Description', 'name' => 'description', 'data' => array()),
            'Author' => array('type' => 'text', 'sub_type' => 'like', 'placeholder' => 'Search Author', 'name' => 'author', 'data' => array()),
            'Source' => array('type' => 'text', 'sub_type' => 'like', 'placeholder' => 'Search Source', 'name' => 'source', 'data' => array()),
            'Published At' => array('type' => 'date', 'sub_type' => '', 'placeholder' => 'Select Published At Date', 'name' => 'published_at', 'data' => []),
            'Tag' => array('type' => 'news_tag_dropdown', 'sub_type' => '', 'placeholder' => 'Select Tag', 'name' => 'news_tag', 'data' => $tags),
//            'Published After' => array('type' => 'date', 'sub_type' => 'range_after', 'placeholder' => 'Select Published After Date', 'name' => 'published_at', 'data' => []),
//            'Published Before' => array('type' => 'date', 'sub_type' => 'range_before', 'placeholder' => 'Select Published Before Date', 'name' => 'published_at', 'data' => []),

        );
    }


    /**
     * @inheritDoc
     */
    public function create()
    {
        $tags = Helper::get_all_content();
        return view('pages.news.add', ['title' => 'Add News', 'tags' => $tags, 'breadcrumbs' => Breadcrumbs::generate('news.create')]);
    }

    public function store(Request $request)
    {
        $rules = News::$validation;
        $validation_rules = $this->validate_lang_tabs(true, true);
        $rules = array_merge($rules, $validation_rules['rules']);
        request()->validate($rules, $validation_rules['customMessages']);
        $news = new News;
        $news->fill(request()->all());
        $news->save();
        if($request->has('tags') && is_array($tags = $request->input('tags')) && count($tags) > 0) {
            $news->tag($tags);
        }
        return Helper::jsonMessage($news->id !== null, News::INDEX_URL);
    }

    /**
     * @inheritDoc
     */
    public function edit($id)
    {
        $tags = Helper::get_all_content();
        $news = News::query()->findOrFail($id);
        return view('pages.news.add', ['title' => 'Edit News', 'tags' => $tags, 'item' => $news, 'breadcrumbs' => Breadcrumbs::generate('news.edit', $news)]);
    }

    public function update(Request $request, $id)
    {
        $rules = News::$validation;
        $validation_rules = $this->validate_lang_tabs(true, true);
        $rules = array_merge($rules, $validation_rules['rules']);
        request()->validate($rules, $validation_rules['customMessages']);
        $news = News::query()->findOrFail($id);
        $news->fill($request->all());
        $news->save();
        if($request->has('tags') && is_array($tags = $request->input('tags')) && count($tags) > 0) {
            $news->retag($tags);
        }
        return Helper::jsonMessage($news !== null, News::INDEX_URL, $news !== null ? 'Record Successfully Updated' : 'Unable to Update');
    }

    /**
     * @inheritDoc
     */
    public function destroy($id)
    {
        try {
            $news = News::query()->findOrFail($id)->delete();
            return Helper::jsonMessage($news !== null, NULL,  $news !== NULL ? 'Record Successfully deleted' : 'Unable to delete record');
        } catch (Exception $e) {
        }
    }
}
