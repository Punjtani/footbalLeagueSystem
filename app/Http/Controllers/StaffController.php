<?php

namespace App\Http\Controllers;

use App\Club;
use App\Helpers\HtmlTemplatesHelper;
use App\Sport;
use App\Staff;
use App\Helpers\Helper;
use App\Team;
use App\Tenant;
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;
use Monarobase\CountryList\CountryListFacade as Countries;

class StaffController extends BaseController
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
                $staff_roles = Sport::query()->select(['roles', 'groups'])->first();
                $team_groups = json_decode($staff_roles['groups'], true, 512, JSON_THROW_ON_ERROR);
                $staff_roles = json_decode($staff_roles['roles'], true, 512, JSON_THROW_ON_ERROR);
                $staff_roles = $staff_roles['staff_roles'];
                $team_groups = $team_groups['team_group'];
                $teams = Team::query()->get();
                return datatables(Staff::filters(request())->select('staff.*'))->addColumn('actions', static function($data){
                    return HtmlTemplatesHelper::get_action_dropdown($data);
                })->addColumn('image', static function ($data){
                    return Helper::get_name_with_image('', $data->image);
                })->addColumn('type', static function ($data) use ($staff_roles){
                    if ($data->type === NULL) {
                        return 'N/A';
                    }
                    return $staff_roles[$data->type] ?? 'N/A';
                })->addColumn('club', static function ($data) use ($teams){
                    if ($data->team_id === NULL) {
                        return 'N/A';
                    }
                    $team = $teams->where('id', $data->team_id)->first();
                    return $team->club->name ?? 'N/A';
                })->addColumn('team', static function ($data) use ($teams, $team_groups){
                    if ($data->team_id === NULL) {
                        return 'N/A';
                    }
                    $team = $teams->where('id', $data->team_id)->first();
                    return $team_groups[$team['team_group']] ?? 'N/A';
                })->rawColumns(['image', 'actions', 'status'])->make(true);
            }
        } catch (Exception $ex){
        }
        return view('pages.staff.list', ['add_url' => route('staff.create'), 'advance_filters' => $this->advance_filters(), 'breadcrumbs' => Breadcrumbs::generate('staff')]);
    }

    private function advance_filters(): array
    {
        $club_list = Club::query()->select('id', 'name')->where('status', Club::STATUS_PUBLISH)->get()->toArray();
        $clubs = array();
        foreach ($club_list as $club) {
            $clubs[$club['id']] = $club['name'];
        }
        $team_groups = Sport::query()->select(['groups'])->first();
        $team_groups = json_decode($team_groups['groups'], true, 512, JSON_THROW_ON_ERROR);
        $team_groups = $team_groups['team_group'];
        $staff_roles = Sport::query()->select(['roles'])->first();
        try {
            $staff_roles = json_decode($staff_roles['roles'], true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
        }
        $staff_roles = $staff_roles['staff_roles'];
        return array(
            'Clubs' => array('type' => 'dropdown', 'sub_type' => '', 'placeholder' => 'Select Club', 'name' => 'club_id', 'data' => $clubs),
            'Teams' => array('type' => 'dropdown', 'sub_type' => '', 'placeholder' => 'Select Team', 'name' => 'team_group', 'data' => $team_groups),
            'Staff Role' => array('type' => 'dropdown', 'sub_type' => '', 'placeholder' => 'Select Staff Role', 'name' => 'type', 'data' => $staff_roles),
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
        $staff_roles = Sport::query()->select(['roles'])->first();
        $staff_roles = json_decode($staff_roles['roles'], true, 512, JSON_THROW_ON_ERROR);
        $clubs = Club::query()->where('status', Team::STATUS_PUBLISH)->get();
        $teams = array();
        $countries = Countries::getList('en', 'php');
        return view('pages.staff.add', ['title' => 'Add Staff', 'countries' => $countries,'clubs' => $clubs, 'teams' => $teams, 'staff_type' => $staff_roles['staff_roles'], 'breadcrumbs' => Breadcrumbs::generate('staff.create')]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
//        request()->validate(Staff::$validation);
        $rules = Staff::$validation;
        $validation_rules = $this->validate_lang_tabs(false);
        $rules = array_merge($rules, $validation_rules['rules']);
        request()->validate($rules, $validation_rules['customMessages']);
        $staff_exist = Staff::query()->where('name', $request->input('name'))
            ->where('country', $request->input('country'))
            ->where('type', $request->input('type'))->count();
        if ($staff_exist > 0) {
            return Helper::jsonMessage(false, NULL, 'Staff With Same Name, Country & Role Already Exists');
        }
        $image = '';
        if ($request->has('image')) {
            $image = $request->file('image');
        }
        $request->request->add(['image' => $image]);
        $staff = new Staff;
        $staff->fill($request->all());
        $staff->save();
        if ( $staff->id !== NULL &&  $request->has('team') ) {
            $staff->team()->attach(Team::query()->find($request->input('team')));
        }
        return Helper::jsonMessage($staff->id !== null, Staff::INDEX_URL);
    }

    /**
     * Display the specified resource.
     *
     * @param Staff $coach
     * @return void
     */
    public function show(Staff $coach)
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
        $staff_roles = Sport::query()->select(['roles', 'groups'])->first();
        $team_groups = json_decode($staff_roles['groups'], true, 512, JSON_THROW_ON_ERROR);
        $team_groups = $team_groups['team_group'];

        $staff_roles = json_decode($staff_roles['roles'], true, 512, JSON_THROW_ON_ERROR);
        $clubs = Club::query()->where('status', Club::STATUS_PUBLISH)->get();
        $staff = Staff::query()->findOrFail($id);
        $temp_teams = Team::query()->select(['id', 'team_group'])->where('id' , $staff->team_id)->where('status', Team::STATUS_PUBLISH)->get();
        $teams = array();
        if($staff->team_id === NULL) {
            $staff->team_id = 0;
            $staff->club_id = 0;
        }else {
            $staff_team = $staff->team;
            $staff->club_id = $staff_team->club_id;
            foreach ($temp_teams as $team) {
                $teams[] = array('id' => $team->id, 'name' => $team_groups[$team->team_group]);
            }
        }
        $countries = Countries::getList('en', 'php');
        return view('pages.staff.add', ['title' => 'Edit Staff', 'countries' => $countries, 'clubs' => $clubs, 'item' => $staff, 'teams' => $teams, 'staff_type' => $staff_roles['staff_roles'], 'breadcrumbs' => Breadcrumbs::generate('staff.edit', $staff)]);
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
        $rules = Staff::$validation;
        $validation_rules = $this->validate_lang_tabs(false);
        $rules = array_merge($rules, $validation_rules['rules']);
        unset($rules['image']);
        request()->validate($rules, $validation_rules['customMessages']);
        $staff_exist = Staff::query()->where('id', '<>', $id)
            ->where('name', $request->input('name'))
            ->where('country', $request->input('country'))
            ->where('type', $request->input('type'))->count();
        if ($staff_exist > 0) {
            return Helper::jsonMessage(false, NULL, 'Staff With Same Name, Country & Role Already Exists');
        }
        $coach = Staff::query()->findOrFail($id);
        if (request()->has('image')) {
            request()->request->add(['image' => request()->file('image')]);
        }
        $coach->fill(request()->all());
        $coach->save();
        return Helper::jsonMessage($coach !== null, Staff::INDEX_URL, $coach !== null ? 'Record Successfully Updated' : 'Unable to Update');
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
            $coach = Staff::query()->findOrFail($id)->delete();
            return Helper::jsonMessage($coach !== null, NULL,  $coach !== NULL ? 'Record Successfully deleted' : 'Unable to delete record');
        } catch (Exception $e) {
        }
    }
}
