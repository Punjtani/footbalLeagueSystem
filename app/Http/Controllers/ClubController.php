<?php

namespace App\Http\Controllers;

use App\Association;
use App\Helpers\Helper;
use App\Helpers\HtmlTemplatesHelper;
use App\Helpers\S3Helper;
use App\Membership;
use App\MembershipLevel;
use App\Player;
use App\Sport;
use App\Stadium;
use App\Tenant;
use Carbon\Carbon;
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Club;
use App\Team;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Monarobase\CountryList\CountryListFacade as Countries;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;
use function MongoDB\BSON\toJSON;

class ClubController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $team_groups = array();

        if (request()->ajax()) {
//                $team_groups = Sport::query()->select(['groups'])->first();
//                $team_groups = json_decode($team_groups['groups'], true, 512, JSON_THROW_ON_ERROR);
            return datatables(Club::filters(request())->with('activeMembershipRelation')->select('clubs.*'))->addColumn('actions', static function ($data) {
                return HtmlTemplatesHelper::get_action_dropdown($data, '', false, true, auth()->user()->can('Teams.Create'), auth()->user()->can('Teams.Delete'),false,false);
            })->addColumn('membership', static function ($data) {
                if (!empty($data->activeMembershipRelation)) {
                    return $data->activeMembershipRelation->membershipLevel->name . " - <span style='font-size: 0.8em'>(" . $data->activeMembershipRelation->created_at->format('d/m/Y H:i') . " - " . $data->activeMembershipRelation->expires_at->format('d/m/Y H:i') . ")</span>";
                } else {
                    return "No Membership";
                }
            })->addColumn('image', static function ($data) {
                return Helper::get_name_with_image('', $data->image);
            })->addColumn('associations', static function ($data) {
                return data_get($data, 'associations.*.name');
            })->addColumn('socials', static function ($data) {
                $html = '';
                $line = false;
                $html .= '<div class="text-truncate">';
                if ($data->facebook !== null && $data->facebook !== '') {
                    $html .= '<span class="btn btn-icon btn-flat-primary"><i class="feather icon-facebook"></i></span>';
                }
                if ($data->twitter !== null && $data->twitter !== '') {
                    $html .= '<span class="btn btn-icon btn-flat-primary"><i class="feather icon-twitter"></i></span>';
                }
                if ($data->instagram !== null && $data->instagram !== '') {
                    $html .= '<span class="btn btn-icon btn-flat-primary"><i class="feather icon-instagram"></i></span>';
                }
                if ($data->youtube !== null && $data->youtube !== '') {
                    $html .= '<span class="btn btn-icon btn-flat-primary"><i class="lab la-youtube"></i></span>';
                }
                if ($html === '') {
                    $html = '<span class="btn btn-sm btn-flat-primary"><i class="feather icon-x"></i></span>';
                }
                $html .= '</div>';
                return $html;
            })->rawColumns(['membership', 'image', 'socials', 'actions', 'status'])->make(true);
        }

        return view('pages.clubs.list', ['add_url' => route("clubs.create"), 'filterData' => $this->advance_filters(), 'team_group' => $team_groups, 'breadcrumbs' => Breadcrumbs::generate('clubs')]);
    }

    private function advance_filters(): array
    {

        return [
            'membershipLevels' => MembershipLevel::pluck('name', 'id'),
        ];
    }

    public function create()
    {
        $stadiums = Stadium::query()->where('status', Stadium::STATUS_PUBLISH)->get();
        $team_groups = Sport::query()->select(['groups'])->first();
        $team_groups = json_decode($team_groups['groups'], true, 512, JSON_THROW_ON_ERROR);
        $team_groups = $team_groups['team_group'];

        return view('pages.clubs.add', ['title' => 'Add Team', 'stadiums' => $stadiums, 'team_groups' => $team_groups, 'breadcrumbs' => Breadcrumbs::generate('clubs.create')]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = Club::$validation;
        $is_default_team = false;
//        $team_group = json_decode($request->input('team_group'), true);
//        if (! isset($team_group['default'])){
//            return Helper::jsonMessage(false, NULL, 'All Checked Team name(s) are required');
//        }
//        foreach ($team_group as $key => $value) {
//            if ($value === NULL || $value === '') {
//                return Helper::jsonMessage(false, NULL, 'All Checked Team name(s) are required');
//            }
//        }

        $validation_rules = $this->validate_lang_tabs(false);
        $rules = array_merge($rules, $validation_rules['rules']);
        request()->validate($rules, $validation_rules['customMessages']);
        $image = '';
        if ($request->has('image')) {
            $image = $request->file('image');
        }
        $request->request->add(['image' => $image]);
        $club = new Club;
        $club->fill($request->all())->save();
//        if ($request->id === '' || $request->id === Null) {
//            $is_default_team = true;
//        }


//        foreach ($team_group as $key => $value) {
//            if ($key === 'default') {
//                $is_default_team = true;
//                $group_name = $value;
//                $team_data = array('name' => $group_name, 'club_id' => $club->id, 'team_group' => $key, 'stadium_id' => $request->stadium_id, 'is_default_team' => $is_default_team, 'status' => $club->getRawOriginal('status'));
//                $team = new Team;
//                $team->fill($team_data)->save();
//            } else {
//                $is_default_team = false;
//                $group_name = $value;
//                $team_data = array('name' => $group_name, 'club_id' => $club->id, 'team_group' => $key, 'stadium_id' => $request->stadium_id, 'is_default_team' => $is_default_team, 'status' => $club->getRawOriginal('status'));
//                $team = new Team;
//                $team->fill($team_data)->save();
//            }
//        }

        return Helper::jsonMessage($club->id !== null, Club::INDEX_URL);
    }

    /**
     * @inheritDoc
     */
    public function show($id)
    {
        $item = Club::findOrFail($id);
//        if($item->memberships()->count() < 1){
//            $firstMembershipLevel = MembershipLevel::where('weight',1)->first();
//            $newMembership = new Membership();
//            $newMembership->club_id = $item ->id;
//            $newMembership->membership_level_id = $firstMembershipLevel->id;
//            $newMembership->status = 1;
//            $newMembership->save();
//            $item = Club::findOrFail($id);
//        }

        return view('pages.clubs.show', [
            'title' => 'Team: ' . $item->name,
            'item' => $item,
            'membershipLevelList' => MembershipLevel::where('status', 1)->pluck('name', 'id')
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $club = Club::query()->findOrFail($id);
        $teams = Team::query()
            ->where('club_id', $id)->get();
        $team_array = array();
        foreach ($teams as $key => $value) {
            $team_array[$value->team_group] = $value->name;
        }

        $team_groups = Sport::query()->select(['groups'])->first();
        $team_groups = json_decode($team_groups['groups'], true, 512, JSON_THROW_ON_ERROR);
        $team_groups = $team_groups['team_group'];
        $stadiums = Stadium::query()->where('status', Stadium::STATUS_PUBLISH)->get();
        return view('pages.clubs.add', ['title' => 'Edit Team', 'stadiums' => $stadiums, 'item' => $club, 'team_groups' => $team_groups, 'selected_groups' => $team_array, 'breadcrumbs' => Breadcrumbs::generate('clubs.edit', $club)]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $rules = Club::$validation;
        unset($rules['image']);
        $rules['name'] = $rules['name'] . ',' . $id;
//        dd($rules);
        $validation_rules = $this->validate_lang_tabs(false);

        $rules = array_merge($rules, $validation_rules['rules']);
        request()->validate($rules, $validation_rules['customMessages']);
        $club = Club::query()->findOrFail($id);
        if (request()->has('image')) {
            request()->request->add(['image' => request()->file('image')]);
        }
        $club->fill(request()->all());
        $club->save();
        Team::query()->where('club_id', $id)->update(['status' => $club->getRawOriginal('status')]);
        return Helper::jsonMessage($club !== null, Club::INDEX_URL, $club !== null ? 'Record Successfully Updated' : 'Unable to Update');
    }

    public function get(Request $request)
    {
        $_id = $request->input('id');
        $club = Club::filters($request)->findOrFail($_id);
        if ($club->image !== null) {
            $club->image = S3Helper::get_image_url($club->image, Club::S3_FOLDER_PATH);
        }
        return response()->json($club);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    // public function destroy($id)
    // {
    //     try {
    //         $club = Club::query()->findOrFail($id)->delete();
    //         return Helper::jsonMessage($club !== null, NULL,  $club !== NULL ? 'Record Successfully deleted' : 'Unable to delete record');
    //     } catch (Exception $e) {
    //     }
    // }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {

        $club = Club::query()->findOrFail($id);
        if ($club->bookings1()->count() > 0 || $club->bookings2()->count() > 0) {
            return Helper::jsonMessage(false, NULL, "Can't delete, Booking exists for this team.");
        }
        try {
            $club->delete();
            return Helper::jsonMessage(true, NULL, 'Record Successfully deleted');
        } catch (Exception $e) {
            return Helper::jsonMessage(false, NULL, $e->getMessage());
        }
    }

    public function get_club_teams(Request $request)
    {
        $temp_teams = Team::query()->select('id', 'team_group')->where('club_id', $request->input('club_id'))->where('status', Team::STATUS_PUBLISH)->get();
        $team_groups = Sport::query()->select(['groups'])->first();
        $team_groups = json_decode($team_groups['groups'], true, 512, JSON_THROW_ON_ERROR);
        $temp_team_groups = $team_groups['team_group'];
        $teams = array();
        foreach ($temp_teams as $team) {
            $teams[] = array('id' => $team->id, 'name' => $temp_team_groups[$team->team_group]);
        }
        return response()->json(['teams' => $teams]);
    }

    public function changeMembership(Request $request)
    {

        $club = Club::findOrFail($request->club_id);

        $activeMembership = $club->activeMembership();
        if ($activeMembership !== null) {
            $activeMembership->status = 0;
            $activeMembership->expires_at = Carbon::now();
            $activeMembership->save();
        }

        $newMembership = new Membership();
        $newMembership->club_id = $club->id;
        $newMembership->membership_level_id = $request->membership_level_id;
        $newMembership->status = 1;
        $newMembership->expires_at = Carbon::now()->add(1, 'year');
        $newMembership->save();
        return redirect(route('clubs.show', $club->id));
    }
}
