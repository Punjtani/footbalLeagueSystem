<?php

namespace App\Http\Controllers;
use App\Association;
use App\Fixture;
use App\Observers\FixtureObserver;
use App\Player;
use App\MatchStatistic;
use App\Club;
use App\Weather;
use App\PointsTable;
use App\SeasonTemplate;
use App\Stadium;
use App\Sport;
use App\Team;
use App\Season;
use App\Stage;
use App\Referee;
use App\Tenant;
use App\Tournament;
use Carbon\Carbon;
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class FixtureController extends BaseController
{
    private $season_id;
    private $teamID;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $teams = Team::query()->where('status', Team::STATUS_PUBLISH)->get();
        $stages = Stage::query()->get();
        $stadiums = Stadium::query()->where('status', Stadium::STATUS_PUBLISH)->get();
        $referees = Referee::query()->get();
        return view('pages.fixtures.add', ['title' => 'Add Fixture', 'teams' => $teams, 'stages' => $stages,'stadiums' => $stadiums, 'referees' => $referees]);
    }

    public function list($id)
    {
        if (!is_numeric($id)){
          abort(403, 'Invalid Season ID.');
        }
        $count = 0;
        $next_stages = array();
        $team_array = array();
        $season_name = Season::where('id',$id)->value('name');
        $this->season_id = $id;
        $stages_fixtures = Stage::with(['fixtures' => function ($query) {
            $query->where('season_id', $this->season_id);
            $query->orderBy('id', 'asc');
        }])->where('season_id', $id)->orderBy('stage_number', 'asc')->get();

        $stadiums = Stadium::query()->where('status', Stadium::STATUS_PUBLISH)->get();
        $season_teams = DB::table('player_season_team')->distinct()->where('season_id', $id)->get('team_id');
        foreach($season_teams as $team => $value){
            $team_array[] = $value->team_id;
        }
        $teams = Team::query()->where('status', Team::STATUS_PUBLISH)->whereIn('id', $team_array )->get();
        $stages = Stage::query()->where('season_id', $id)->orderBy('stage_number', 'asc')->get();
        foreach($stages as $stage => $val){
            $fixture_status =  Fixture::Select('match_status')->where('season_id', $id)->where('stage_id', $val->id)->where('match_status', '<>', 'completed')->get();
            if(!empty($fixture_status)) {
                if ($count > 0) {
                    $next_stages[] = $val->id;
                }
                if (count($fixture_status) > 0) {
                    $count++;
                }
            }
        }
        $points_table_view = true;
        foreach($stages_fixtures as $stages => $stag_val){
            foreach($stag_val->fixtures as $fixture => $fix_val){
                if($fix_val->match_status !== 'completed'){
                    $points_table_view = false;
                }
            }
        }

        $tournament_id =  Season::where('id', $this->season_id)->get('tournament_id');
        $tournament =  Tournament::query()->findOrFail($tournament_id[0]->tournament_id);
        return view('pages.fixtures.list', ['points_table_view' => $points_table_view ,'stages_fixtures' => $stages_fixtures, 'teams' => $teams, 'season_name' => $season_name, 'stadiums' => $stadiums, 'season_id' => $this->season_id, 'next_stages' => $next_stages, 'breadcrumbs' => Breadcrumbs::generate('fixtures.list', $id, $tournament)]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    public function points_table($id)
    {
        if (!is_numeric($id)){
            abort(403, 'Invalid Season ID.');
        }
//        $sport_types = Config::get('custom.sport_types');
        $season_name = Season::where('id',$id)->value('name');
        if (auth()->user()->role !== Config::get('app.ROLE_ADMIN')) {
            $sport_id = Tenant::query()->select(['sport_id'])->findOrFail(auth()->user()->tenant_id);
        } else {
            $sport_id = Tenant::query()->select(['sport_id'])->where('status', Tenant::STATUS_PUBLISH)->first();
        }
        $sports = Sport::query()->where('status', Sport::STATUS_PUBLISH)->where('id',$sport_id->sport_id)->get();

        foreach($sports as $sport => $val) {
            $scoring = json_decode($val->scoring);
            $sports_name = strtolower($val->sport_name);
        }
        foreach($scoring as $score => $value){
           $score_data = $value->$sports_name;
        }
        $tournament_id =  Season::where('id', $id)->value('tournament_id');
        $tournament_name = Tournament::where('id', $tournament_id)->value('name');
        $points_data = '';
//        $points_table = PointsTable::query()->distinct('team_id')->where('tournament_id', $tournament_id)->orderBy('team_id', 'asc')->get();
        $points_table = PointsTable::query()->where('tournament_id', $tournament_id)->orderBy('team_id', 'asc')->get();
        if(count($points_table) > 0) {
            foreach ($points_table as $key => $value) {
                $tournament_id = $value->tournament_id;
                $team_name = Team::find($value->team_id)->name;
                $score_point_data = json_decode($value->team_points);
                foreach($score_point_data as $k => $v){
                    $equation = $v->eq;
                    $eq_array = explode(' ', $v->eq);
                     foreach($eq_array as $keys => $val){
                         if(property_exists($v,$val)) {
                             $replace = $v->$val;
                             $equation = str_replace($val, $replace, $equation);
                         }
                    }
                        $calculated_points = eval('return '.$equation.';');
                       $points_data .= '<tr><td>' . $team_name . '</td><td> <input type="number" id="gp_'.$value->id.'" value="'.$v->GP.'"></td><td><input type="number"  id="w_'.$value->id.'" value="'.$v->W.'"></td><td><input type="number" id="l_'.$value->id.'" value="'.$v->L.'"></td><td><input type="number" id="d_'.$value->id.'" value="'.$v->D.'"></td><td><input type="number"  id="gf_'.$value->id.'" value="'.$v->GF.'"></td><td><input type="number" id="ga_'.$value->id.'" value="'.$v->GA.'"></td><td><input type="number" id="gd_'.$value->id.'" value="'.$v->GD.'"></td><td><input type="number" id="p_'.$value->id.'" value="'.$calculated_points.'"></td><td><input type="button" data-btnID="'.$value->id.'" class="btn_save" id="btn_'.$value->id.'" value="Save"></td></tr>' . PHP_EOL;
                }
            }
        }
        return view('pages.fixtures.points_table', ['points_data' => $points_data, 'season_id' => $id, 'season_name' => $season_name,'score_data' => $score_data, 'tournament_name' => $tournament_name]);
    }


    public function detail($id)
    {
        if (!is_numeric($id)){
            abort(403, 'Invalid Fixture ID.');
        }
        $team_a_img = '';
        $team_b_img = '';
        $teamA = '';
        $teamB = '';
        $stadium_name = '';
        $current_season_id = '';
        $fixture_id = $id;
        $stats = array();
        $team_a_id = '';
        $team_b_id = '';
        $club_A = '';
        $club_B = '';
        $win_team_name = '';
        $fixture_detail = Fixture::query()->where('id', $id)->orderBy('id', 'asc')->get();
        if(count($fixture_detail) > 0) {
            foreach ($fixture_detail as $key => $value) {
                $team_a_id = $value->team_a_id;
                $team_b_id = $value->team_b_id;
                $match_day = $value->match_day;
                $match_status = $value->match_status;
                $teamA_score = $value->team_a_score;
                $teamB_score = $value->team_b_score;
                $current_season_id = $value->season_id;
                $stage_name = Stage::find($value->stage_id)->name;
                if(!empty($value->team_a_id)) {
                    $teamA = Team::find($value->team_a_id)->name;
                    $club_A =  Team::find($team_a_id)->ClubName;
                    $team_a_img = Team::find($value->team_a_id)->image;
                }
                if(!empty($value->team_b_id)) {
                    $team_b_img = Team::find($value->team_b_id)->image;
                    $teamB = Team::find($value->team_b_id)->name;
                    $club_B =  Team::find($team_b_id)->ClubName;
                }
                if(!empty($value->stadium_id)) {
                    $stadium_name = Stadium::find($value->stadium_id)->name;
                }

                if($match_status === 'completed'){
                    if ($value->match_result === 'team_a_win'){
                        $win_team_name =  Team::find($value->team_a_id)->name;
                    }else if ($value->match_result === 'team_b_win'){
                        $win_team_name =  Team::find($value->team_b_id)->name;
                    }else if ($value->match_result === 'draw') {
                        $win_team_name = 'Draw';
                    }else {
                        $win_team_name = '';
                    }
                }
            }
        }
        // Getting players which already saved for playing 11, substitute, none
        $matchA_squad_players = DB::table('match_squad')->where('match_id', $fixture_id)->where('team_id', $team_a_id)->get();
        $matchB_squad_players = DB::table('match_squad')->where('match_id', $fixture_id)->where('team_id', $team_b_id)->get();
        //End
        $stats_info = MatchStatistic::query()->where('match_id', $fixture_id)->get();

        //Getting Team A Players
        $teamA_player_array = array();
        $teamB_player_array = array();

        $team_A_players = DB::table('player_season_team')->distinct()->where('season_id', $current_season_id)->where('team_id', $team_a_id)->get('player_id');
                foreach($team_A_players as $team_a_player => $value){
                    $teamA_player_array[] = $value->player_id;
                }
        $teamA_players = Player::query()->where('status', Player::STATUS_PUBLISH)->whereIn('id', $teamA_player_array)->get();
            //Getting Team B Players

        $team_B_players = DB::table('player_season_team')->distinct()->where('season_id', $current_season_id)->where('team_id', $team_b_id)->get('player_id');
             foreach($team_B_players as $team_b_player => $value){
                    $teamB_player_array[] = $value->player_id;
                }
        $teamB_players = Player::query()->where('status', Player::STATUS_PUBLISH)->whereIn('id', $teamB_player_array)->get();

        // Getting players for Team A which already saved for playing 11 and Substitute
        $teamA_playing11_players = Helper::get_team_players($team_a_id, $fixture_id, array('playing','yellow_card'));
        $teamA_substitute_players = Helper::get_team_players($team_a_id, $fixture_id, array('substitute'));
        // Getting players for Team B which already saved for playing 11 and Substitute
        $teamB_playing11_players = Helper::get_team_players($team_b_id, $fixture_id, array('playing','yellow_card'));
        $teamB_substitute_players = Helper::get_team_players($team_b_id, $fixture_id, array('substitute'));
        //End Team Players

        if (auth()->user()->role !== Config::get('app.ROLE_ADMIN')) {
            $sport_id = Tenant::query()->select(['sport_id'])->findOrFail(auth()->user()->tenant_id);
        } else {
            $sport_id = Tenant::query()->select(['sport_id'])->where('status', Tenant::STATUS_PUBLISH)->first();
        }
            $sports = Sport::query()->where('status', Sport::STATUS_PUBLISH)->where('id', $sport_id->sport_id)->get();
            $stadiums = Stadium::query()->where('status', Stadium::STATUS_PUBLISH)->get();
            foreach($sports as $sport => $val) {
                $sport_id = $val->id;
                $stats = json_decode($val->stats);
            }

        $stats_data = '';
        $latest_stats = MatchStatistic::query()->where('match_id', $id)->orderBy('id', 'desc')->get();
        if(count($latest_stats) > 0) {
            foreach ($latest_stats as $key => $value) {
                $player_name = Player::find($value->player_id)->name;
                $team_name = Team::find($value->team_id)->name;
                $stats_data .= '<tr><td>' . $value->minute_of_action . '</td><td>'. ucfirst($team_name) . '</td><td>' . $player_name . '</td><td>' . str_replace('_', ' ', strtoupper($value->stat_key)) . '</tr>' . PHP_EOL;
            }
        }
        $weather = Weather::query()->where('fixture_id', $fixture_id)->orderBy('id', 'desc')->first();
        $tournament_id =  Season::where('id', $current_season_id)->value('tournament_id');
        $tournament =  Tournament::query()->findOrFail($tournament_id);
        return view('pages.fixtures.detail', ['tournament_id' => $tournament_id, 'win_team_name' => $win_team_name, 'stadiums' => $stadiums, 'teamA' => $teamA, 'teamB' => $teamB, 'stadium_name' => $stadium_name, 'team_a_img' => $team_a_img, 'team_b_img' => $team_b_img, 'stats' => $stats, 'sport_id' => $sport_id, 'fixture_id' => $fixture_id,'teamA_players' => $teamA_players, 'teamB_players' => $teamB_players, 'team_a_id' =>$team_a_id, 'team_b_id' =>$team_b_id, 'matchA_squad_players' => $matchA_squad_players, 'matchB_squad_players' => $matchB_squad_players,'season_id' => $current_season_id, 'club_A' => $club_A, 'club_B' => $club_B, 'teamA_playing11_players' => $teamA_playing11_players, 'teamA_substitute_players' => $teamA_substitute_players, 'teamB_playing11_players' =>$teamB_playing11_players, 'teamB_substitute_players' =>$teamB_substitute_players, 'match_day' => $match_day, 'stage_name' => $stage_name, 'teamA_score' => $teamA_score, 'teamB_score' =>$teamB_score, 'stats_data' => $stats_data, 'match_status' => $match_status, 'weather' => $weather, 'stats_info' => $stats_info, 'breadcrumbs' => Breadcrumbs::generate('fixtures.detail', $id, $current_season_id , $tournament) ]);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $data = array(
            'match_day' => $request->input('matchday'),
            'team_a_id' => $request->input('team_a'),
            'team_b_id' => $request->input('team_b'),
            'scheduled_date' => $request->input('schedule_date'),
//            'match_status' => $request->input('status'),
            'stadium_id' => $request->input('stadium')
        );

        if($request->input('team_a')  !== $request->input('team_b')) {
            $fixture = Fixture::find($request->input('fixture_id'));
            $fixture->fill($data);
            $fixture->save();
            return Helper::jsonMessage($fixture, NULL, $fixture !== null ? 'Fixture Successfully Updated.' : 'Unable to Update Fixture.');
        } else {
            return Helper::jsonMessage(NULL, NULL, 'Please Choose Different Team.');
        }
    }
    public function update_playing11(Request $request)
    {
         $result = '';
         $playing_count = 0;
         $team_players =  $request->input('playerArray');
        if(!isset($team_players)){
            return Helper::jsonMessage(NULL, NULL, 'Please Select at least One Player.');
        }
        foreach($team_players as $player_id => $option_val) {
            if($option_val == 'playing'){
                $playing_count++;
            }
        }
        if($playing_count > 11) {
            return Helper::jsonMessage(NULL, NULL, 'You Cannot Select Playing Players more then 11');
        }
        $match_squad_player = DB::table('match_squad')->where('match_id',$request->input('fixture_id'))->where('team_id',$request->input('team_id'))->get();
       // DB::table('match_squad')->where('team_id',$request->input('team_id') )->delete();
        foreach($team_players as $player_id => $option_val) {
            if(count($match_squad_player) > 0 ) {
                $result =  DB::table('match_squad')
                    ->where('match_id',$request->input('fixture_id'))
                    ->where('team_id',$request->input('team_id'))
                    ->where('player_id',$player_id)
                    ->update(['player_status' => $option_val]);
            } else {
                $result =  DB::table('match_squad')->insert(
                ['match_id' => $request->input('fixture_id'), 'player_id' => $player_id, 'team_id' => $request->input('team_id'), 'player_status' => $option_val]
            );
            }
        }

        if($result) {
            try {
                $fixture = Fixture::query()->find($request->input('fixture_id'));
                (new FixtureObserver())->dynamo_update($fixture);
            } catch (Exception $ex) {

            }
            return Helper::jsonMessage($result, NULL, $result !== null ? 'Playing 11 Players Successfully Updated.' : 'Unable to Save Playing 11 Player.');
        } else {
            return Helper::jsonMessage(NULL, NULL, 'Unable to Save Playing 11 Player.');
        }
    }

    public function update_matchstatus(Request $request){
        $data = array(
          'match_status' => $request->input('match_status')
        );

        $auto_points_table =    $request->input('pointstable_auto');
        $tournament_id =    $request->input('tournament_id');
        $season_id =    $request->input('season_id');
        $fixture = Fixture::find($request->input('fixture_id'));
        $fixture->fill($data);
        $fixture->save();

        if($data['match_status'] === 'completed'){
            $teamA_score = $fixture->team_a_score;
            $teamB_score = $fixture->team_b_score;
            $teamA_id = $fixture->team_a_id;
            $teamB_id = $fixture->team_b_id;
            if($teamA_score > $teamB_score ){
                $win_team =  'team_a_win';
                $this->insert_point_table($tournament_id, $teamA_id, $season_id, $auto_points_table);

            }else if ($teamB_score > $teamA_score) {
                $win_team = 'team_b_win';
                $this->insert_point_table($tournament_id, $teamB_id, $season_id, $auto_points_table);

            }else if ($teamA_score !== 0 && $teamB_score !== 0 && $teamB_score == $teamA_score) {
                $win_team = 'draw';
                $this->insert_point_table($tournament_id, $teamA_id, $season_id, $auto_points_table);
                $this->insert_point_table($tournament_id, $teamB_id, $season_id, $auto_points_table);
            }else {
                $win_team = 'draw';
            }
             Fixture::where('id',$request->input('fixture_id'))
                ->update(['match_result' => $win_team]);
        }

        try {
            $fixture = Fixture::query()->find($request->input('fixture_id'));
            (new FixtureObserver())->dynamo_update($fixture);
        } catch (Exception $ex) {

        }
        return Helper::jsonMessage($fixture, NULL, $fixture !== null ? 'Match Status Successfully Updated.' : 'Unable to Update Match Status.');
    }

    public function insert_point_table($tournamentID, $teamID, $season_id, $auto_points_table){

        $total_match = 0;
        $win = 0;
        $lost = 0;
        $draw = 0;
        $goal_for = 0;
        $goals_against = 0;
        $goal_diff = 0 ;
        if($auto_points_table){
            $this->teamID = $teamID;
            $data = Fixture::where('season_id' , $season_id)
                ->where(function($q) {
                    $q->where('team_a_id', $this->teamID)
                        ->orWhere('team_b_id', $this->teamID);
                })
                ->get();
            $total_match = count($data);
            foreach($data as $key => $value){
                  $match_id = $value->id;
                if($value->match_status === 'completed'){
                    if ($value->match_result === 'team_a_win'){
                        $win_team =  $value->team_a_id;
                        $lost_team = $value->team_b_id;
                    }else if ($value->match_result === 'team_b_win'){
                        $win_team =  $value->team_b_id;
                        $lost_team = $value->team_a_id;
                    }else if ($value->match_result === 'draw') {
                        $win_team = 'Draw';
                        $draw++;
                    }else {
                        $win_team = '';
                        $lost_team = '';
                    }
                    if($win_team == $teamID){
                        $win++;
                        $goal_for += MatchStatistic::query()->where('match_id',$match_id)->where('team_id',$teamID)->where('stat_key','goal')->get()->count();
                        $goals_against += MatchStatistic::query()->where('match_id',$match_id)->where('team_id',$lost_team)->where('stat_key','goal')->get()->count();
                    }
                }
            }
        }
        $lost = $total_match - $win;
        $goal_diff = $goal_for - $goals_against;
            $points_data =  json_encode(array(
            'football' => array(
                "GP" => $total_match,
                "W" => $win,
                "L" => $lost,
                "D" => $draw,
                "GF" => $goal_for,
                "GA" => $goals_against,
                "GD" => $goal_diff,
                "P" => '0',
                "eq" => 'W * 2 + D',
            )
        ), JSON_THROW_ON_ERROR);

        $point_table = new PointsTable;
        $point_table->team_id = $teamID;
        $point_table->tournament_id = $tournamentID;
        $point_table->team_points = $points_data;
        $point_table->save();

    }
    public function points_update(Request $request){
        $id = $request->input('ID');
        $data =  json_encode(array(
            'football' => array(
                "GP" => $request->input('GP'),
                "W" => $request->input('W'),
                "L" => $request->input('L'),
                "D" => $request->input('D'),
                "GF" => $request->input('GF'),
                "GA" => $request->input('GA'),
                "GD" => $request->input('GD'),
                "P" => $request->input('Pts'),
                "eq" => 'W * 2 + D',
            )
        ), JSON_THROW_ON_ERROR);

        $result =  PointsTable::where('id', $id)
            ->update(['team_points' => $data]);

        return Helper::jsonMessage($result, NULL, $result !== null ? 'Points Table Successfully Updated.' : 'Unable to Update Points Table.');
    }

    public function update_player_substitute(Request $request)
    {
        $team_id =  $request->input('team_id');
        $match_id =  $request->input('fixture_id');

        if(empty($request->input('team_id'))){
            return Helper::jsonMessage(NULL, NULL, 'Please Select Team.');
        }
        if(empty($request->input('playing_11_player'))){
            return Helper::jsonMessage(NULL, NULL, 'Please Select Playing Player');
        }
        if(empty($request->input('substitute_player'))){
            return Helper::jsonMessage(NULL, NULL, 'Please Select Substitute Player.');
        }
        if(empty($request->input('minutes_of_action'))){
            return Helper::jsonMessage(NULL, NULL, 'Please Enter Minute of Action.');
        }
        $result =  DB::table('match_squad')
            ->where('match_id',$match_id)
            ->where('team_id',$team_id)
            ->where('player_id',$request->input('playing_11_player'))
            ->update(['player_status' => 'substituted', 'substituted_by' => $request->input('substitute_player'), 'minutes_of_action' => $request->input('minutes_of_action')]);

        $result =  DB::table('match_squad')
            ->where('match_id',$match_id)
            ->where('team_id',$team_id)
            ->where('player_id',$request->input('substitute_player'))
            ->update(['player_status' => 'playing']);

        $substitute_player_id = $request->input('substitute_player');
        $substituted_player_id = $request->input('playing_11_player');

        $message['type']    = 'Success';
        $message['message'] = 'Player Successfully Substituted.';
        $message['icon']    = 'check';
        $message['substitute_player_id']  = $substitute_player_id;
        $message['substituted_player_id']  = $substituted_player_id;
        echo json_encode($message);
        try {
            $fixture = Fixture::query()->find($request->input('fixture_id'));
            (new FixtureObserver())->dynamo_update($fixture);
        } catch (Exception $ex) {

        }
    }

    public function get_team_player(Request $request)
    {
          $teamid =  $request->input('team_val');
          $fixture_id =  $request->input('fixture_id');
          $team_players = Helper::get_team_players($teamid, $fixture_id, array('playing','yellow_card'));
          $str = '<option value = "">Select Playing Player</option>' . PHP_EOL;
         if(count($team_players) > 0) {
            foreach ($team_players as $key => $val) {
                $str .= '<option value =' . $val->id . '>' . Helper::get_default_lang($val->name) .' (' .ucfirst($val->playing_position) . ') ' . '</option>' . PHP_EOL;
            }
         }

        echo json_encode($str);
    }

    public function get_teamsplayer_list(Request $request)
    {
        $teamid =  $request->input('team_val');
        $fixture_id =  $request->input('fixture_id');
        $team_img =  Team::find($teamid)->image;
        // Getting Playing Player
        $team_playing_players = Helper::get_team_players($teamid, $fixture_id, array('playing','yellow_card'));
        $playing = '<option value = "">Select Playing Player</option>' . PHP_EOL;
        if(count($team_playing_players) > 0) {
            foreach ($team_playing_players as $key => $val) {
                $playing .= '<option value =' . $val->id . '>' . Helper::get_default_lang($val->name) .' (' .ucfirst($val->playing_position) . ') ' . '</option>' . PHP_EOL;
            }
        }
        // Getting Substitution Player
        $team_substitution_players = Helper::get_team_players($teamid, $fixture_id, array('substitute'));
        $substitution = '<option value = "">Select Substitute Player</option>' . PHP_EOL;
        if(count($team_substitution_players) > 0) {
            foreach ($team_substitution_players as $key => $val) {
                $substitution .= '<option value =' . $val->id . '>' . Helper::get_default_lang($val->name) .' (' .ucfirst($val->playing_position) . ') '. '</option>' . PHP_EOL;
            }
        }
        $message['playing_player']    = $playing;
        $message['substitution_player'] = $substitution;
        $message['team_img'] = $team_img;
        echo json_encode($message);
    }

    public function stats_update(Request $request)
    {
        $action_type =  $request->input('action_type');
        $is_own_goal = 0;
        $teamArray =  explode('_', $request->input('team_id'));
        $teamName = $teamArray[0];
        $teamID = $teamArray[1];
        if($teamName == 'TeamA'){
            $team_id_column = 'team_a_id';
            $team_score_column = 'team_a_score';
        }else {
            $team_id_column = 'team_b_id';
            $team_score_column = 'team_b_score';
        }

        $sports = Sport::query()->where('status', Sport::STATUS_PUBLISH)->where('id', $request->input('sport_id'))->get();

         foreach($sports as $sport => $val) {
            $scoring = json_decode($val->scoring);
        }
        foreach($scoring as $score => $value){
            if(isset($value->$action_type)) {
                $action_value = $value->$action_type;
            }
        }

        if($action_type == 'goal') {

            $data = Fixture::where('season_id',$request->input('season_id'))->where('id',$request->input('fixture_id'))->where($team_id_column,$teamID)->get($team_score_column);
            $previous_score = $data[0]->$team_score_column;
            if($previous_score == 0){
                $new_score = $action_value;
            }else {
                $new_score = $previous_score + $action_value;
            }

            Fixture::where('season_id',$request->input('season_id'))
                ->where('id',$request->input('fixture_id'))
                ->where($team_id_column,$teamID)
                ->update([$team_score_column => $new_score]);
        }

        if($action_type == 'penalty'){
            if($request->input('isGoal') === 'yes') {
                $data = Fixture::where('season_id',$request->input('season_id'))->where('id',$request->input('fixture_id'))->where($team_id_column,$teamID)->get($team_score_column);
                $previous_score = $data[0]->$team_score_column;
                if ($previous_score == 0) {
                    $new_score = $action_value;
                } else {
                    $new_score = $previous_score + $action_value;
                }
                Fixture::where('season_id',$request->input('season_id'))
                    ->where('id',$request->input('fixture_id'))
                    ->where($team_id_column,$teamID)
                    ->update([$team_score_column => $new_score]);
            }
        }

        if($action_type == 'own_goal'){

            if($teamName == 'TeamA'){
                $data = Fixture::where('season_id',$request->input('season_id'))->where('id',$request->input('fixture_id'))->get('team_b_score');
                $previous_score = $data[0]->team_b_score;
                if ($previous_score == 0) {
                    $new_score = $action_value;
                } else {
                    $new_score = $previous_score + $action_value;
                }
                Fixture::where('season_id',$request->input('season_id'))
                    ->where('id',$request->input('fixture_id'))
                    ->update(['team_b_score' => $new_score]);

            } else {
                $data = Fixture::where('season_id',$request->input('season_id'))->where('id',$request->input('fixture_id'))->get('team_a_score');
                $previous_score = $data[0]->team_a_score;

                if ($previous_score == 0) {
                    $new_score = $action_value;
                } else {
                    $new_score = $previous_score + $action_value;
                }

                Fixture::where('season_id',$request->input('season_id'))
                    ->where('id',$request->input('fixture_id'))
                    ->update(['team_a_score' => $new_score]);
            }
            $is_own_goal = 1;
        }

        $score_data = Fixture::where('season_id',$request->input('season_id'))->where('id',$request->input('fixture_id'))->where($team_id_column,$teamID)->get();
        $teamA_score = $score_data[0]->team_a_score;
        $teamB_score = $score_data[0]->team_b_score;
        $data = array(
            'match_id' => $request->input('fixture_id'),
            'team_id' => $teamID,
            'stat_key' => $request->input('action_type'),
            'player_id' => $request->input('player_id'),
            'minute_of_action' => $request->input('minutes'),
            'stat_value' => json_encode(
                array(
                    'TeamA' => $teamA_score,
                    'TeamB' => $teamB_score
            )),
            'is_own_goal' => $is_own_goal
        );

        $result = MatchStatistic::create($data);

        $substituted_player_id = '';
        $yellow_card = '';
        $double_yellow_card = '';
        if($action_type == 'red_card') {
            DB::table('match_squad')
                ->where('match_id',$request->input('fixture_id'))
                ->where('team_id',$teamID)
                ->where('player_id',$request->input('player_id'))
                ->update(['player_status' => 'red_card']);
            $substituted_player_id = $request->input('player_id');
        }

        if($action_type == 'yellow_card') {
            $yellow_card_data = DB::table('match_statistics')->where('match_id',$request->input('fixture_id'))->where('team_id',$teamID)->where('player_id',$request->input('player_id'))->where('stat_key','yellow_card')->get();
             if(count($yellow_card_data) >= 2){
                 DB::table('match_squad')
                     ->where('match_id',$request->input('fixture_id'))
                     ->where('team_id',$teamID)
                     ->where('player_id',$request->input('player_id'))
                     ->update(['player_status' => 'two_yellow_card']);
                // $substituted_player_id = $request->input('player_id');
                 $double_yellow_card = $request->input('player_id');
             } else {
                 DB::table('match_squad')
                     ->where('match_id',$request->input('fixture_id'))
                     ->where('team_id',$teamID)
                     ->where('player_id',$request->input('player_id'))
                     ->update(['player_status' => 'yellow_card']);
                 $yellow_card = $request->input('player_id');
             }
        }
        $str = '';
        $latest_stats = MatchStatistic::query()->where('match_id', $request->input('fixture_id'))->orderBy('id', 'desc')->get();
        if(count($latest_stats) > 0) {
            foreach ($latest_stats as $key => $value) {
                $player_name = Player::find($value->player_id)->name;
                $team_name = Team::find($value->team_id)->name;
                $str .= '<tr><td>' . $value->minute_of_action . '</td><td>'. ucfirst($team_name) . '</td><td>' . $player_name . '</td><td>' . str_replace('_', ' ', strtoupper($value->stat_key)) . '</tr>' . PHP_EOL;
            }
        }
        $message['type']    = 'Success';
        $message['message'] = 'Player Stats Updated';
        $message['icon']    = 'check';
        $message['latest_data']    = $str;
        $message['teamA_score']    = $teamA_score;
        $message['teamB_score']    = $teamB_score;
        $message['substituted_player_id']  = $substituted_player_id;
        $message['yellow_card']  = $yellow_card;
        $message['double_yellow_card']  = $double_yellow_card;
        echo json_encode($message);
        try {
            $fixture = Fixture::query()->find($request->input('fixture_id'));
            (new FixtureObserver())->dynamo_update($fixture);
        } catch (Exception $ex) {

        }
    }

    public function weather_update(Request $request)
    {
        $weather = new Weather;
        $weather->fixture_id = $request->input('fixture_id');
        $weather->temp = $request->input('temp');
        $weather->temp_feels_like = $request->input('temp_feels_like');
        $weather->unit = $request->input('unit');
        $weather->weather_type =  $request->input('weather_type');
        $weather->save();

        $weather = Weather::query()->where('fixture_id', $request->input('fixture_id'))->orderBy('id', 'desc')->first();
        $message['type']    = 'Success';
        $message['message'] = 'Weather Successfully Updated';
        $message['icon']    = 'check';
        $message['weather']    = $weather;
        echo json_encode($message);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
