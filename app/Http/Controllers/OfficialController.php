<?php

namespace App\Http\Controllers;

use App\Helpers\HtmlTemplatesHelper;
use App\Official;
use App\Sport;
use App\Helpers\Helper;
use App\Team;
use App\Tenant;
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Monarobase\CountryList\CountryListFacade as Countries;

class OfficialController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return Factory|View
     */
    public function index()
    {
        try {
            if (request()->ajax()) {
                $official_roles = Sport::query()->select(['roles'])->first();
                $official_roles = json_decode($official_roles['roles'], true, 512, JSON_THROW_ON_ERROR);
                $official_roles = $official_roles['officials'];
                $teams = Team::query()->get();
                return datatables(Official::filters(request()))->addColumn('actions', static function($data){
                    return HtmlTemplatesHelper::get_action_dropdown($data);
                })->addColumn('image', static function ($data){
                    return Helper::get_name_with_image('', $data->image);
                })->addColumn('type', static function ($data) use ($official_roles){
                    if ($data->type === NULL) {
                        return 'N/A';
                    }
                    return $official_roles[$data->type] ?? 'N/A';
                })->addColumn('team', static function ($data) use ($teams){
//                    if ($data->team_id === NULL) {
//                        return 'N/A';
//                    }
//                    $team = $teams->where('id', $data->team_id)->first();
//                    return $team['name'] ?? 'N/A';
                })->rawColumns(['image', 'actions', 'status'])->make(true);
            }
        } catch (Exception $ex){
        }
        return view('pages.officials.list', ['add_url' => route('officials.create'), 'advance_filters' => $this->advance_filters(), 'breadcrumbs' => Breadcrumbs::generate('officials')]);
    }

    private function advance_filters(): array
    {

        $official_roles = Sport::query()->select(['roles'])->first();

        try {
            $official_roles = json_decode($official_roles['roles'], true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
        }
        $official_roles = $official_roles['officials'];
        return array(
            'Roles' => array('type' => 'dropdown', 'sub_type' => '', 'placeholder' => 'Select Role', 'name' => 'type', 'data' => $official_roles),
            'Country' => array('type' => 'dropdown', 'sub_type' => '', 'placeholder' => 'Select Country', 'name' => 'country', 'data' => Countries::getList('en', 'php')),
            );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Factory|View
     */
    public function create()
    {
        $official_roles = Sport::query()->select(['roles'])->first();
        $official_roles = json_decode($official_roles['roles'], true, 512, JSON_THROW_ON_ERROR);
//        $teams = Team::query()->where('status', Team::STATUS_PUBLISH)->get();
        $countries = Countries::getList('en', 'php');
        return view('pages.officials.add', ['title' => 'Add Official', 'countries' => $countries, 'official_type' => $official_roles['officials'], 'breadcrumbs' => Breadcrumbs::generate('officials.create')]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $rules = Official::$validation;
        $validation_rules = $this->validate_lang_tabs(false);
        $rules = array_merge($rules, $validation_rules['rules']);
        request()->validate($rules, $validation_rules['customMessages']);
        $official_exist = Official::query()->where('name', $request->input('name'))
            ->where('country', $request->input('country'))
            ->where('type', $request->input('type'))->count();
        if ($official_exist > 0) {
            return Helper::jsonMessage(false, NULL, 'Official With Same Name, Country & Role Already Exists');
        }
        $image = '';
        if ($request->has('image')) {
            $image = $request->file('image');
        }
        $request->request->add(['image' => $image]);
        $official = new Official;
        $official->fill($request->all());
        $official->save();
//        if ( $official->id !== NULL &&  $request->has('team') ) {
//            $official->team()->attach(Team::query()->find($request->input('team')));
//        }
        return Helper::jsonMessage($official->id !== null, Official::INDEX_URL);
    }

    /**
     * Display the specified resource.
     *
     * @param Official $officials
     * @return void
     */
    public function show(Official $referee)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $id
     * @return Factory|View
     */
    public function edit($id)
    {
        $official_roles = Sport::query()->select(['roles'])->first();
        $official_roles = json_decode($official_roles['roles'], true, 512, JSON_THROW_ON_ERROR);
        $official_roles = $official_roles['officials'];
//        $teams = Team::query()->where('status', Team::STATUS_PUBLISH)->get();
        $officials = Official::query()->findOrFail($id);
        $countries = Countries::getList('en', 'php');
        return view('pages.officials.add', ['title' => 'Edit Official', 'countries' => $countries, 'item' => $officials, 'official_type' => $official_roles, 'breadcrumbs' => Breadcrumbs::generate('officials.edit', $officials)]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $rules = Official::$validation;
        $validation_rules = $this->validate_lang_tabs(false);
        $rules = array_merge($rules, $validation_rules['rules']);
        unset($rules['image']);
        request()->validate($rules, $validation_rules['customMessages']);
        $official_exist = Official::query()->where('id', '<>', $id)
            ->where('name', $request->input('name'))
            ->where('country', $request->input('country'))
            ->where('type', $request->input('type'))->count();
        if ($official_exist > 0) {
            return Helper::jsonMessage(false, NULL, 'Official With Same Name, Country & Role Already Exists');
        }
        $officials = Official::query()->findOrFail($id);
        if (request()->has('image')) {
            request()->request->add(['image' => request()->file('image')]);
        }
        $officials->fill(request()->all());
        $officials->save();
        return Helper::jsonMessage($officials !== null, Official::INDEX_URL, $officials !== null ? 'Record Successfully Updated' : 'Unable to Update');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy($id): ?JsonResponse
    {
        try {
            $officials = Official::query()->findOrFail($id)->delete();
            return Helper::jsonMessage($officials !== null, NULL,  $officials !== NULL ? 'Record Successfully deleted' : 'Unable to delete record');
        } catch (Exception $e) {
        }
    }
}
