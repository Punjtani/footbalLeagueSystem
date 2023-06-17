<?php

namespace App\Http\Controllers;

use App\Club;
use App\Helpers\Helper;
use App\Helpers\HtmlTemplatesHelper;
use App\Http\Requests\StorePlayerRequest;
use App\Http\Requests\UpdatePlayerRequest;
use App\Observers\ClubObserver;
use App\Observers\PlayerObserver;
use App\Player;
use App\Sport;
use App\Team;
use App\Tenant;
use Carbon\Carbon;
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Monarobase\CountryList\CountryListFacade as Countries;

class PlayerController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return Factory|View
     * @throws Exception
     */
    public function index()
    {
        $player_roles = Sport::query()->select(['roles', 'groups'])->first();
        try {
            if (request()->ajax()) {
                $team_groups = json_decode($player_roles['groups'], true, 512, JSON_THROW_ON_ERROR);
                $player_roles = json_decode($player_roles['roles'], true, 512, JSON_THROW_ON_ERROR);
                $player_roles = $player_roles['player_roles'];
                $team_groups = $team_groups['team_group'];
                return datatables(Player::filters(request())->select('players.*'))->addColumn('actions', static function($data) {
                    $html = "<a class='dropdown-item migrate-player' data-item='". $data->id ."'>Migrate</a>";
                    return HtmlTemplatesHelper::get_action_dropdown($data, $html);
                })->addColumn('image', static function ($data){
                    return Helper::get_name_with_image('', $data->image);
                })->addColumn('player_role', static function ($data) use ($player_roles){
                    return $player_roles[$data->playing_position];
                })->addColumn('jersey_number', static function ($data) {
                    if ($data->jersey === NULL) {
                        return 'N/A';
                    }
                    return [$data->jersey];
                })->addColumn('club_name', static function ($data) {
                    $club = $data->club()->where('left_on', NULL)->first();
                    if ($club !== NULL) {
                        return $club->name;
                    }
                    return 'N/A';
                })->addColumn('team_name', static function ($data) use ($team_groups) {
                    $team = $data->teams()->latest('joined_on')->first();
                    $club = $data->club()->where('left_on', NULL)->first();
                    if ($team !== NULL && $club !== NULL && $team->club_id === $club->id) {
                        return $team_groups[$team->team_group];
                    }
                    return 'N/A';
                })->rawColumns(['image', 'socials', 'actions', 'status'])->make(true);
            }
            $player_roles = json_decode($player_roles['roles'], true, 512, JSON_THROW_ON_ERROR);
            $clubs = Club::query()->get();
            return view('pages.players.list', ['clubs' => $clubs, 'teams' => [], 'add_url' => route("players.create"), 'playing_positions' => $player_roles['player_roles'], 'advance_filters' => $this->advance_filters(), 'breadcrumbs' => Breadcrumbs::generate('players')]);
        } catch (Exception $ex){
        }
    }

    private function advance_filters(): array
    {
        $club_list = Club::query()->select('id', 'name')->where('status', Club::STATUS_PUBLISH)->get()->toArray();
        $clubs = array();
        foreach ($club_list as $club) {
            $clubs[$club['id']] = $club['name'];
        }


        try {
            $player_roles = Sport::query()->select(['roles', 'groups'])->first();
            $teams = json_decode($player_roles['groups'], true, 512, JSON_THROW_ON_ERROR);
            $teams = $teams['team_group'];
            $player_roles = json_decode($player_roles['roles'], true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
        }
        $player_roles = $player_roles['player_roles'];
        return array(
            'Club' => array('type' => 'dropdown', 'sub_type' => '', 'placeholder' => 'Select Club', 'name' => 'club_id', 'data' => $clubs),
            'Team' => array('type' => 'dropdown', 'sub_type' => '', 'placeholder' => 'Select Team', 'name' => 'team_id', 'data' => $teams),
            'Player Role' => array('type' => 'dropdown', 'sub_type' => '', 'placeholder' => 'Select Player Role', 'name' => 'playing_position', 'data' => $player_roles),
            'Country' => array('type' => 'dropdown', 'sub_type' => '', 'placeholder' => 'Select Country', 'name' => 'country', 'data' => Countries::getList('en', 'php')),
            'Socials' => array('type' => 'dropdown', 'sub_type' => '', 'placeholder' => 'Select Social page', 'name' => 'socials', 'data' => ['facebook' => 'Facebook', 'twitter' => 'Twitter', 'instagram' => 'Instagram', 'youtube' => 'Youtube']),
//            'Date Of Birth' => array('type' => 'date', 'sub_type' => '', 'placeholder' => 'Select Date Of Birth', 'name' => 'dob', 'data' => []),
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Factory|View
     */
    public function create()
    {
        try {
            $player_roles = Sport::query()->select(['roles'])->first();
            $player_roles = json_decode($player_roles['roles'], true, 512, JSON_THROW_ON_ERROR);
            $countries = Countries::getList('en', 'php');
            $clubs = Club::query()->where('status', Club::STATUS_PUBLISH)->get();
            $teams = array();
            return view('pages.players.add', ['title' => 'Add Player', 'countries' => $countries, 'playing_positions' => $player_roles['player_roles'], 'clubs' => $clubs, 'teams' => $teams, 'breadcrumbs' => Breadcrumbs::generate('players.create')]);
        } catch (Exception $e) {

        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePlayerRequest $request
     * @return JsonResponse
     */
    public function store(StorePlayerRequest $request): JsonResponse
    {
        $player_exist = Player::query()->where('name', $request->input('name'))
            ->where('dob', $request->input('dob'))
            ->where('playing_position', $request->input('playing_position'))
            ->where('country', $request->input('country'))
            ->where('jersey', $request->input('jersey'))->count();
        if ($player_exist > 0) {
            return Helper::jsonMessage(false, NULL, 'Player With Same Name, Playing Position, Country, DOB & Jersey Already Exists');
        }
        $image = '';
        if ($request->has('image')) {
            $image = $request->file('image');
        }
        $request->request->add(['image' => $image]);
        $player = new Player;
        $player->fill($request->all());
        $player->save();
        if($request->has('club') && $request->input('club') !== '' && $request->has('club_joining_date')) {
            $club_id = $request->input('club');
            $player->club()->attach($club_id, ['joined_on' => $request->input('club_joining_date')]);
            if ($request->has('team') && $request->input('team') !== '' && $request->has('team_joining_date')) {
                $team_id = $request->input('team');
                $player->teams()->attach($team_id, ['joined_on' => $request->input('team_joining_date')]);
            }
        }
        return Helper::jsonMessage($player->id !== null, Player::INDEX_URL);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $id
     * @return Factory|View
     * @throws \JsonException
     */
    public function edit($id)
    {
        $player = Player::query()->findOrFail($id);
        $temp_club = $player->club()->where('left_on', NULL)->first();
        $player->club_id = $temp_club !== NULL ? $temp_club->id : 0;
        $temp_team = $player->teams()->latest('joined_on')->first();
        $player->team_id = 0;
        $clubs = Club::query()->where('status', Club::STATUS_PUBLISH)->get();
        if ($temp_club !== NULL) {
            $temp_teams = Team::query()->select(['id', 'team_group'])->where('status', Team::STATUS_PUBLISH)->where('club_id', $temp_club->id)->get();
            $teams = array();
            $team_groups = Sport::query()->select(['roles', 'groups'])->first();
            $team_groups = json_decode($team_groups['groups'], true, 512, JSON_THROW_ON_ERROR);
            $team_groups = $team_groups['team_group'];
            foreach ($temp_teams as $team) {
                $teams[] = array('id' => $team->id, 'name' => $team_groups[$team->team_group]);
            }
            $player->club_joining_date = Carbon::make($temp_club->pivot->joined_on)->toDateString();
        } else {
            $teams = array();
        }
        if ($temp_team !== NULL && $temp_club !== NULL && $temp_team->club_id === $temp_club->id) {
            $player->team_id = $temp_team->id;
            $player->team_joining_date = Carbon::make($temp_team->pivot->joined_on)->toDateString();
        }
        $player_roles = Sport::query()->select(['roles'])->first();
        $player_roles = json_decode($player_roles['roles'], true, 512, JSON_THROW_ON_ERROR);
        $countries = Countries::getList('en', 'php');
        return view('pages.players.add', ['title' => 'Edit Player', 'countries' => $countries, 'item' => $player, 'playing_positions' => $player_roles['player_roles'], 'clubs' => $clubs, 'teams' => $teams, 'breadcrumbs' => Breadcrumbs::generate('players.edit', $player)]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePlayerRequest $request
     * @param $id
     * @return JsonResponse
     */
    public function update(UpdatePlayerRequest $request, $id): JsonResponse
    {
        $player_exist = Player::query()->where('id', '<>', $id)
            ->where('name', $request->input('name'))
            ->where('dob', $request->input('dob'))
            ->where('playing_position', $request->input('playing_position'))
            ->where('country', $request->input('country'))
            ->where('jersey', $request->input('jersey'))->count();
        if ($player_exist > 0) {
            return Helper::jsonMessage(false, NULL, 'Player With Same Name, Playing Position, Country, DOB & Jersey Already Exists');
        }
        $player = Player::query()->findOrFail($id);
        if (request()->has('image')) {
            request()->request->add(['image' => request()->file('image')]);
        }
        $player->fill(request()->all());
        $player->save();
        return Helper::jsonMessage($player !== null, Player::INDEX_URL, $player !== null ? 'Record Successfully Updated' : 'Unable to Update');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy($id)
    {
        try {
            $player = Player::query()->findOrFail($id)->delete();
            return Helper::jsonMessage($player !== null, NULL,  $player !== NULL ? 'Record Successfully deleted' : 'Unable to delete record');
        } catch (Exception $e) {
        }
    }

    /**
     * Display the specified resource.
     *
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \JsonException
     */
    public function get(Request $request): JsonResponse
    {
        $team_groups = Sport::query()->select(['groups'])->first();
        $team_groups = json_decode($team_groups['groups'], true, 512, JSON_THROW_ON_ERROR);
        $team_groups = $team_groups['team_group'];
        $player = Player::query()->findOrFail($request->input('player_id'));
        $team = $player->teams()->latest('joined_on')->first();
        $club = $player->club()->where('left_on', NULL)->first();
        $player->current_club = 0;
        $player->current_team = 0;
        $player->current_club_joining_date = NULL;
        $player->current_team_joining_date = NULL;
        if ($club !== NULL) {
            $player->current_club = $club->id;
            $player_joined_on = DB::table('club_player')->select('joined_on')->where('player_id', $player->id)->whereNull('left_on')->first();
            $player->current_club_joining_date = $player_joined_on !== NULL ? $player_joined_on->joined_on : NULL;
            if ($team !== NULL && $team->club_id === $club->id) {
                $player->current_team = $team->id;
                $player->current_team_joining_date = $team->pivot->joined_on;
            }
            $temp_teams = Team::query()->select('id', 'team_group')->where('club_id', $club->id)->where('status', Team::STATUS_PUBLISH)->get();
            $teams = array();
            foreach ($temp_teams as  $team) {
                $teams[] = array('id' => $team->id, 'name' => $team_groups[$team->team_group]);
            }
        }
        return response()->json(['item' => $player, 'teams' => $teams]);
    }

    /**
     * Migrate Player From [Club, Team] to [Club, Team]
     * @param Request $request
     * @return JsonResponse
     */
    public function migrate(Request $request): JsonResponse
    {
        $player = Player::query()->findOrFail($request->input('id'));
        $current_club = $player->club()->first();
        $current_club = $current_club !== NULL ? $current_club->id : 0;
        $club_id = $request->input('club');
        $club_leaving_date = $request->input('club_leaving_date');
        $club_joining_date = $request->input('club_joining_date');
        $team_id = $request->input('team');
        $playing_position = $request->input('playing_position');
        $jersey = $request->input('jersey');
        $current_team = $player->teams()->latest('joined_on')->first();
        $team_joining_date = $request->input('team_joining_date');
        $player_club_joined_on = $request->input('old_club_joining_date');
        $player_team_joined_on = $request->input('old_team_joining_date');
        // Validations
        if ($current_club !== (int)$club_id) {
            $message = '';
            if ($club_joining_date === NULL) {
                $message = 'New Club Joining Date is required';
            }
            if ($club_leaving_date === NULL) {
                $message = 'Current Club Leaving Date is required';
            }
            if ($team_id !== NULL && $team_joining_date === NULL && ( $current_team === NULL || $current_team->club_id !== $club_id )) {
                $message = 'New Team Joining Date is required';
            }
            if ($player_club_joined_on !== NULL && Carbon::parse($player_club_joined_on)->gt(Carbon::parse($club_leaving_date))) {
                $message = 'Current Club Leaving Date Should be After Current Club Joining Date';
            }
            if ($player_team_joined_on !== NULL && Carbon::parse($player_team_joined_on)->gt(Carbon::parse($team_joining_date))) {
                $message = 'New Team Joining Date Should be After Current Team Joining Date';
            }
            if (Carbon::parse($club_joining_date)->lt(Carbon::parse($club_leaving_date))) {
                $message = 'New Club Joining Date Should not be Before Current Club Leaving Date';
            }
            if (Carbon::parse($team_joining_date)->lt(Carbon::parse($club_joining_date))) {
                $message = 'New Team Joining Date Should not be Before New Club Joining Date';
            }
            if ($message !== '') {
                return Helper::jsonMessage(false, NULL, $message);
            }
        }
        if ($club_id === NULL && $club_leaving_date !== NULL) { // CLUB less
            DB::table('club_player')->where('player_id', $request->input('id'))->whereNull('left_on')->update(['left_on' => $club_leaving_date]);
        } else {
            if ($club_leaving_date !== NULL) {
                DB::table('club_player')->where('player_id', $request->input('id'))->whereNull('left_on')->update(['left_on' => $club_leaving_date]);
            }
            if ($current_club !== $club_id && $club_joining_date !== NULL) {
                DB::table('club_player')->insert(['club_id' => $club_id, 'player_id' => $request->input('id'), 'joined_on' => $club_joining_date, 'left_on' => NULL]);
            }
        }
        if ($team_id !== NULL && $team_joining_date !== NULL && ($current_team === NULL || $current_team->club_id !== $club_id)) {
            DB::table('player_team')->insert(['player_id' => $request->input('id'), 'team_id' => $team_id, 'joined_on' => $team_joining_date, 'jersey' => ($jersey ?? 0), 'playing_position' => $playing_position]);
        }
        if ($jersey !== NULL) {
            $player->jersey = $jersey;
        }

        if ($playing_position !== NULL) {
            $player->playing_position = $playing_position;
        }
        if (request()->has('image')) {
            $player->image = request()->file('image');
        }
        $player->save();
        if ($player !== NULL) {
            (new PlayerObserver())->dynamo_update($player);
        }
        return Helper::jsonMessage($player !== null, Player::INDEX_URL, $player !== null ? 'Player Successfully Transferred' : 'Unable to Transfer Player');
    }
}
