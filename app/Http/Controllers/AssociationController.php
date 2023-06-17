<?php

namespace App\Http\Controllers;

use App\Association;
use App\Helpers\Helper;
use App\Helpers\S3Helper;
use App\Helpers\HtmlTemplatesHelper;
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Monarobase\CountryList\CountryListFacade as Countries;

class AssociationController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    public function index()
    {
        try {
            if (request()->ajax()) {
                return datatables(Association::filters(request()))->addColumn('actions', static function($data){
                    return HtmlTemplatesHelper::get_action_dropdown($data);
                })->addColumn('image', static function ($data) {
                    return Helper::get_name_with_image('', $data->image);
                })->rawColumns(['image', 'actions', 'status'])->make(true);
            }
        } catch (Exception $ex) {

        }
        return view('pages.associations.list', ['breadcrumbs' => Breadcrumbs::generate('associations')]);
    }

    /**
     * @inheritDoc
     */
    public function create()
    {
        $countries = Countries::getList('en', 'php');
        return view('pages.associations.add', ['title' => 'Add Association', 'countries' => $countries]);
    }

    /**
     * @inheritDoc
     */
    public function store(Request $request)
    {
//        request()->validate(Association::$validation);
        $rules = Association::$validation;
        $validation_rules = $this->validate_lang_tabs();
        $rules = array_merge($rules, $validation_rules['rules']);
        request()->validate($rules, $validation_rules['customMessages']);
        $image = '';
        if ($request->has('image')) {
            $image = $request->file('image');
        }
        $request->request->add(['image' => $image]);
        $association = new Association;
        $association->fill($request->all())->save();
        return Helper::jsonMessage($association->id !== null, Association::INDEX_URL);
    }

    /**
     * @inheritDoc
     */
    public function edit($id)
    {
        $association = Association::query()->findOrFail($id);
        $countries = Countries::getList('en', 'php');
        return view('pages.associations.add', ['title' => 'Edit Association', 'countries' => $countries, 'item' => $association, 'breadcrumbs' => Breadcrumbs::generate('associations.edit', $association)]);
    }

    /**
     * @inheritDoc
     */
    public function update(Request $request, $id)
    {
        $rules = Association::$validation;
        $validation_rules = $this->validate_lang_tabs();
        $rules = array_merge($rules, $validation_rules['rules']);
        unset($rules['image']);
        request()->validate($rules, $validation_rules['customMessages']);
        $association = Association::query()->findOrFail($id);
        if (request()->has('image')) {
            request()->request->add(['image' => request()->file('image')]);
        }
        $association->fill(request()->all());
        $association->save();
        return Helper::jsonMessage($association !== null, Association::INDEX_URL, $association !== null ? 'Record Successfully Updated' : 'Unable to Update');
    }

    /**
     * @inheritDoc
     */
    public function show($id)
    {
        // TODO: Implement show() method.
    }

    /**
     * @inheritDoc
     */
    public function destroy($id)
    {
        try {
            $association = Association::query()->findOrFail($id)->delete();
            return Helper::jsonMessage($association !== null, NULL,  $association !== NULL ? 'Record Successfully deleted' : 'Unable to delete record');
        } catch (Exception $e) {
        }
    }
}
