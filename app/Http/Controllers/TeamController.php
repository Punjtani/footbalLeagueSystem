<?php

namespace App\Http\Controllers;

use App\Helpers\HtmlTemplatesHelper;
use App\Staff;
use App\Sport;
use App\Helpers\Helper;
use App\Helpers\S3Helper;
use App\Player;
use App\Stadium;
use App\Club;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Team;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;


class TeamController extends BaseController
{
    private $club_id;
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
                return datatables(Team::filters(request()))->addColumn('actions', static function($data){
//                    $html = '<a title="associate players" class="dropdown-item associate-players" id="' . $data->id . '" onclick="window.location=' . "'" . route('teams.players', ['id' => $data->id]) . "'" . '">Associate Players</a>';
                    return HtmlTemplatesHelper::get_action_dropdown($data, '', false);
                })->addColumn('image', static function ($data){
                    return Helper::get_name_with_image('', $data->image);
                })->addColumn('club_name', static function ($data){
                    return $data->club->name;
                })->rawColumns(['image', 'actions', 'status'])->make(true);
            }
        } catch (Exception $ex){
        }
        return view('pages.teams.list');

    }

    /**
     * @inheritDoc
     */
    public function create()
    {
        $clubs = Club::query()->where('status', Club::STATUS_PUBLISH)->get();
        $stadiums = Stadium::query()->where('status', Stadium::STATUS_PUBLISH)->get();
        $staff = Staff::query()->where('status', Staff::STATUS_PUBLISH)->get();
        $team_groups = Sport::query()->select(['roles'])->first();
        $team_groups = json_decode($team_groups['roles'], true, 512, JSON_THROW_ON_ERROR);
        $team_groups = $team_groups['team_group'];
        return view('pages.teams.add', ['title' => 'Add Team', 'staff' => $staff, 'clubs' => $clubs, 'stadiums' => $stadiums, 'team_group'=> $team_groups]);
    }

    /**
     * @inheritDoc
     */
    public function store(Request $request)
    {
        $rules = Team::$validation;
        $validation_rules = $this->validate_lang_tabs(false);
        $is_default_team = false;
        $rules = array_merge($rules, $validation_rules['rules']);
        request()->validate($rules, $validation_rules['customMessages']);

//        if($request->club_id === '' || $request->club_id === null) {
//            $image = '';
//            if ($request->has('image')) {
//                $image = $request->file('image');
//            }
//            $is_default_team = true;
//            $club_data = array('name' => $request->name, 'description' => $request->description, 'founding_date' => $request->founding_date, 'status' => $request->status, 'image' => $image, 'stadium_id' => $request->stadium_id, 'facebook' => $request->facebook, 'instagram' => $request->instagram, 'twitter' => $request->twitter);
//            $club = new Club;
//            $club->fill($club_data)->save();
//        }
        $team = new Team;
        $team->fill($request->all());
        $team->club_id = $request->input('club_id');
        if($is_default_team !== false){
            $team->is_default_team = $is_default_team;
            $team->team_group = $request->input('group');
        }else{
            $team->club_id = $request->input('club_id');
            $team->is_default_team = $is_default_team;
            $team->team_group = $request->input('group');
        }
        $team->save();
//        $team->club_id = $club !== NULL ? $club->id : $request->input('club_id');
        return Helper::jsonMessage($team->id !== null, Club::INDEX_URL);
    }

    /**
     * @inheritDoc
     */
    public function edit($id)
    {
        $stadiums = Stadium::query()->where('status', Stadium::STATUS_PUBLISH)->get();
        $team = Team::query()->findOrFail($id);
        $clubs = Club::query()->where('id', $team->club_id)->get();
        $team_players = $team->players;
        $captain = $team->captains->where('left_on', NULL)->first();
        $team->captain = $captain !== NULL ? $captain->id : 0;
        $staff = Staff::query()->where('status', Staff::STATUS_PUBLISH)->get();
        return view('pages.teams.add', ['title' => 'Edit Team', 'staff' => $staff, 'item' => $team, 'clubs'=>$clubs, 'stadiums' => $stadiums, 'players' => $team_players]);
    }

    /**
     * @inheritDoc
     */
    public function update(Request $request, $id)
    {
        $rules = Team::$validation;
        $validation_rules = $this->validate_lang_tabs();
        $rules = array_merge($rules, $validation_rules['rules']);
        request()->validate($rules, $validation_rules['customMessages']);
        $team = Team::query()->findOrFail($id);
        if (request()->has('image')) {
            request()->request->add(['image' => request()->file('image')]);
        }
        $team->fill(request()->all());
        $team->save();
        $captain = $team->captains->where('left_on', NULL)->first();
        $team->captains()->syncWithoutDetaching([$captain->id => ['left_on' => Carbon::now()], $request->input('captain') => ['joined_on' => Carbon::now()]]);
        return Helper::jsonMessage($team !== null, Team::INDEX_URL, $team !== null ? 'Record Successfully Updated' : 'Unable to Update');
    }

    /**
     * @param $id
     * @return Factory|JsonResponse|View|mixed
     */
    public function associate_player_form($id)
    {
        try {
            if (request()->ajax()) {
                $club_id = Team::query()->select('club_id')->find($id);
                $club = Club::query()->find($club_id->club_id);
                $club_player = $club->players;
                return datatables($club_player)->addColumn('teams', static function($data){
                    $teams = $data->teams;
                    $team_name = 'N/A';
                    foreach ($teams as $team_data){
                        $team_name = $team_data['name'];
                    }
                    return $team_name;
                })->addColumn('selected', static function ($data) use ($id) {
                    $player_in_squad = DB::table('player_team')->where('team_id', $id)->where('player_id', $data['id'])->count();
                    if ($player_in_squad > 0) {
                        return 'selected';
                    }
                    return 'false';
                })->addColumn('player_id', static function($data){
                    return $data['id'];
                })->addColumn('current_team_id', static function($data) use ($id) {
                    return $id;
                })->addColumn('id', static function($data) {
                    return '';
                })->addColumn('image', static function($data) {
//                    return $data->getAttributes()['image'];
                })->rawColumns(['status', 'teams'])->make(true);
            }
        } catch (Exception $ex){
        }
        return view('pages.teams.associatePlayers', ['item' => Team::query()->findOrFail($id)]);
    }

    /**
     * @inheritDoc
     */
    public function associate_players(Request $request, $id)
    {
        $player = Arr::flatten($request->input('players'));
        $team = Team::query()->find( $id);
        $player_team = Player::with('teams')->get()->toArray();
        foreach ($player_team as $item => $value) {
            if(in_array($value['id'], $player)){
                foreach ($value['teams'] as $item_team => $team_value) {
                    if($team_value['id'] !== $id){
                        DB::table('player_team')
                            ->where('player_id', $value['id'])
                            ->update(['team_id' => $team_value['id'], 'left_on'=>Carbon::now()]);
                    }
                }
            }
        }
        $team->players()->attach($player, ['joined_on'=>Carbon::now()]);
//        $team = Team::query()->findOrFail($id);
//        $players_old = data_get($team->players, '*.player_id') ?? array();
//        $players_new = $request->input('players');
//        $players_left = array_diff($players_old, data_get($players_new, '*.player_id'));
//        $this->remove_players_from_other_teams($players_new, $team->associations, $id);
//        $team->players_history = $this->add_players_to_history($players_left, $team->players ?? array(), $team->players_history);
//        $team->players = $players_new;
//        $team->save();
        return Helper::jsonMessage($team !== null, Team::INDEX_URL, $team !== null ? 'Players Successfully Added' : 'Unable to Add Players');
    }

    /**
     * @param $players
     * @param $old_current
     * @param $history
     * @return array|null
     */
    private function add_players_to_history ($players, $old_current, $history) {
        if ($history === null || empty($history))
            $history = array();
        foreach ($old_current as $player) {
            if (collect($players)->contains($player['player_id'])) {
                $player['left_on'] = Carbon::now()->toDateString();
                $history[] = $player;
            }
        }
        return $history;
    }

    private function remove_players_from_other_teams ($players, $associations, $current_team_id) {
        $associations = data_get($associations, '*.association_id');
        foreach ($players as $player) {
            $teams = Team::query()->whereIn('associations.association_id', Arr::flatten($associations))->where('id', '<>', $current_team_id)->where('players.player_id', $player['player_id'])->get();
            foreach ($teams as $team) {
                $team_players = $team->players;
                $history = $team->players_history;
                foreach ($team_players as $key => $team_player) {
                    if ($team_player['player_id'] === $player['player_id']) {
                        $team_player['left_on'] = Carbon::now()->toDateString();
                        $history[] = $team_player;
                        unset($team_players[$key]);
                    }
                }
                $team->players_history = $history;
                sort($team_players, SORT_NUMERIC);
                $team->players = $team_players;
                $team->save();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function get(Request $request)
    {
        $_id = $request->input('id');
        $team = Team::filters($request)->findOrFail($_id);
        if ($team->image !== null)
            $team->image = S3Helper::get_image_url($team->image, Team::S3_FOLDER_PATH);
        return response()->json($team);
    }


    /**
     * @inheritDoc
     */
    public function destroy($id)
    {
        try {
            $team = Team::query()->findOrFail($id)->delete();
            return Helper::jsonMessage($team !== null, NULL,  $team !== NULL ? 'Record Successfully deleted' : 'Unable to delete record');
        } catch (Exception $e) {
        }
    }
}
