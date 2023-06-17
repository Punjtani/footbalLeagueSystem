<?php

namespace App\Http\Controllers;

use App\Fixture;
use App\Helpers\Helper;
use App\Helpers\HtmlTemplatesHelper;
use App\Http\Requests\StoreSeasonRequest;
use App\Observers\SeasonObserver;
use App\PlayerSeasonTeam;
use App\Season;
use App\SeasonTemplate;
use App\Sport;
use App\Stage;
use App\Team;
use App\Tournament;
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Monarobase\CountryList\CountryListFacade as Countries;

class SeasonController extends BaseController
{
    /**
     * @param null $tournament_id
     * @return Application|Factory|JsonResponse|View|mixed
     */
    public function index($tournament_id = NULL)
    {
        try {
            if (request()->ajax()) {
                return datatables(Season::filters(request())->where('tournament_id', $tournament_id))->addColumn('actions', static function ($data) use ($tournament_id) {
                    $html = '';
                    if($data->getRawOriginal('status') === Season::STATUS_PUBLISH) {
                        $html = '<a title="assign season squads" class="dropdown-item" onclick="window.location=' . "'" . route('seasons.season_teams', ['id' => $data->id]) . "'" . '">Season Squads</a>';
                        $html .= ' <a href="' . route('fixtures.list', ['id' => $data->id]) . '" class="dropdown-item">Fixtures</a> ';
                    }
//                    if (auth()->user()->role !== Config::get('app.ROLE_ADMIN')) {
//                        $html .= '<a title="edit" class="dropdown-item" onclick="window.location=' . "'" . route('seasons.edit', ['id' => $data->id, 'tournament_id' => $tournament_id]) . "'" . '">Edit</a>';
//                    }
                    return HtmlTemplatesHelper::get_action_dropdown($data, $html, false, true, false);
                })->addColumn('tournament', static function ($data) {
                    $tournament = $data->tournament()->first();
                    if ($tournament === NULL) {
                        return 'N/A';
                    }
                    return Helper::get_default_lang($tournament['name']);
                })->rawColumns(['actions', 'status'])->make(true);
            }
        } catch (Exception $ex) {

        }
        return view('pages.seasons.list', ['add_url' => route('seasons.create', ['tournament_id' => $tournament_id]),'tournament_id' => $tournament_id, 'breadcrumbs' => Breadcrumbs::generate('seasons', Tournament::query()->findOrFail($tournament_id))]);

    }

    /**
     * @param $tournament_id
     * @return Application|Factory|View
     */
    public function create($tournament_id)
    {
        $tournament =  Tournament::query()->findOrFail($tournament_id);
        $season_templates = SeasonTemplate::query()->where('status', SeasonTemplate::STATUS_PUBLISH)->get();
        return view('pages.seasons.add', ['title' => 'Add Season', 'tournament' => $tournament, 'season_templates' => $season_templates, 'breadcrumbs' => Breadcrumbs::generate('seasons.create', $tournament)]);
    }

    /**
     * @param StoreSeasonRequest $request
     * @return JsonResponse
     * @throws \JsonException
     */
    public function store(StoreSeasonRequest $request): JsonResponse
    {
        $season = new Season;
        $season->fill($request->all())->save();
        if (((int)$request->input('status')) === Season::STATUS_PUBLISH) {
            $template = SeasonTemplate::query()->findOrFail($request->input('season_template_id'));
            $configuration = $template->configuration;
            $number_of_teams = $template->number_of_teams;
            $type = $template->getRawOriginal('type');
            $stages = array();
            $i = 1;
            foreach ($configuration as $name => $conf) {
                $stage = new Stage;
                $stage->name = $conf['name'];
                $stage->stage_number = $i;
                $stage->season_id = $season->getAttributeValue('id');
                $stage->type = $conf['type'];
                $tab = array();
                $tab['home_and_away'] = $conf['home_and_away'];
                if ($conf['type'] === 1 || $conf['type'] === '1') { // Round Robin
                    $tab['teams_forward_from_group'] = $conf['number_of_teams_forward'] ?? 0;
                    $tab['groups_count'] = $conf['number_of_groups'] ?? 1;
                    $tab['round_robin_type'] = $conf['round_robin_type'];
                } else { // Knockout
                    $tab['third_place'] = $conf['third_place'] ?? 0;
                }
                $stage->configuration = json_encode($tab, JSON_THROW_ON_ERROR);
                $stage->save();
                $stages[$i] = array('type' => $conf['type'], 'id' => $stage->id, 'home_and_away' => $tab['home_and_away'], 'round_robin_repetition' => $tab['round_robin_type'] ?? 1, 'teams_forward_from_group' => $tab['teams_forward_from_group'] ?? 1, 'groups_count' => $tab['groups_count'] ?? 1);
                $i++;
            }
            if ($type === 3) { // Round Robin
                $teams = array();
                $groups = $stages[1]['groups_count'];
                $team_number = 0;
                $teams_less = $number_of_teams % $groups;
                if ($teams_less === 1) {
                    $teams_in_a_group = (int)floor($number_of_teams / $groups);
                } else if ($teams_less === 0) {
                    $teams_in_a_group = $number_of_teams / $groups;
                } else {
                    $teams_in_a_group = (int)ceil($number_of_teams / $groups);
                }
                $group = 1;
                for ($i = 1 ; $i <= $number_of_teams ; $i++) {
                    if ($request->has('team_' . $team_number)) {
                        $team = $request->input('team_' . $team_number++);
                        $season->teams()->attach($team, ['group' => $group]);
                        $teams[$group][] = $team;
                    }
                    if ($i % $teams_in_a_group === 0 && $i !== $number_of_teams - 1) {
                        $group++;
                    }
                }
            } else { // Knockout && League
                $teams = array();
                for ($i = 0 ; $i < $number_of_teams ; $i++) {
                    if ($request->has('team_' . $i)) {
                        $team = $request->input('team_' . $i);
                        $season->teams()->attach($team);
                        $teams[] = $team;
                    }
                }
                if ($type === 2) {
                    $teams = array($teams);
                }
            }
            // Match day, season ID, Stage ID
            $teams_count = $number_of_teams;
            foreach ($stages as $stage_number => $stage) {
                $home_and_away = $stage['home_and_away'] === '1';
                if ($stage['type'] === '0') { // Knock out
                    for ($i = 0; $i < $teams_count; $i += 2) {
                        $team_a = $team_b = NULL;
                        if ($stage_number === 1) {
                            $team_a = $teams[$i];
                            $team_b = $teams[$i + 1];
                        }
                        $this->create_fixture($season->id, $stage['id'], $team_a, $team_b);
                        if ($home_and_away) {
                            $this->create_fixture($season->id, $stage['id'], $team_b, $team_a, 2);
                        }
                    }
                    $teams_count /= 2;
                } else { // Round Robin && League
                    $teams_forward = $stage['teams_forward_from_group'];
                    $round_robin_type = $stage['round_robin_repetition'];
                    $teams_count = 0;
                    foreach ($teams as $group) {
                        $teams_in_group = count($group);
                        for ($i = 0; $i < $teams_in_group; $i++) {
                            for ($j = $i + 1; $j < $teams_in_group; $j++) {
                                for ($k = 0; $k < (int)$round_robin_type; $k++) {
                                    $this->create_fixture($season->id, $stage['id'], $group[$i], $group[$j]);
                                    if ($home_and_away) {
                                        $this->create_fixture($season->id, $stage['id'], $group[$j], $group[$i], 2);
                                    }
                                }
                            }
                        }
                        $teams_count += $teams_forward;
                    }
                }
            }
        }
        return Helper::jsonMessage($season->id !== null, Season::INDEX_URL, 'Record successfully added', ['tournament_id' => $request->input('tournament_id') ?? 1]);
    }

    private function create_fixture($season_id, $stage_id, $team_a_id = NULL, $team_b_id = NULL, $match_day = 1): void
    {
        $fixture = new Fixture();
        $data = array('match_day' => $match_day, 'season_id' => $season_id, 'stage_id' => $stage_id, 'team_a_id' => $team_a_id, 'team_b_id' => $team_b_id);
        $fixture->fill($data)->save();
    }

    /**
     * @param $id
     * @param $tournament_id
     * @return Application|Factory|View
     */
    public function edit($id, $tournament_id)
    {
        $season = Season::query()->findOrFail($id);
        $tournament =  Tournament::query()->findOrFail($tournament_id);
        $season_templates = SeasonTemplate::query()->where('status', SeasonTemplate::STATUS_PUBLISH)->get();
        return view('pages.seasons.add', ['title' => 'Edit Season', 'item' => $season, 'tournament' => $tournament, 'season_templates' => $season_templates, 'breadcrumbs' => Breadcrumbs::generate('seasons.edit', $season, $tournament)]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        request()->validate(Season::$validation);
        $season = Season::query()->findOrFail($id);
        if (request()->has('image')) {
            request()->request->add(['image' => request()->file('image')]);
        }
        $season->fill(request()->all());
        $season->save();
        return Helper::jsonMessage($season !== null, Season::INDEX_URL, $season !== null ? 'Record Successfully Updated' : 'Unable to Update');
    }

    /**
     * @param $id
     */
    public function show($id)
    {
        // TODO: Implement show() method.
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        try {
            $season = Season::query()->findOrFail($id)->delete();
            return Helper::jsonMessage($season !== null, NULL, $season !== NULL ? 'Record Successfully deleted' : 'Unable to delete record');
        } catch (Exception $e) {
        }
    }

    /**
     * @param $tournament_id
     * @return JsonResponse|mixed
     */
    public function get_teams_list($tournament_id)
    {
        try {
            if (request()->ajax()) {
                $tournament = Tournament::query()->select('team_group')->findOrFail($tournament_id);
                return datatables(Team::query()->with('club')->where('status', Team::STATUS_PUBLISH)->where('team_group', $tournament->team_group))->rawColumns([])->make(true);
            }
        } catch (Exception $ex) {

        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse|mixed
     */
    public function get_season_teams(Request $request, $id)
    {
        try {
            if (request()->ajax()) {
                $team_ids = DB::table('player_season_team')->select('team_id')->distinct('team_id')->where('season_id', $id)->get()->toArray();
                $team_ids = data_get($team_ids, '*.team_id');
                $teams = Team::query()->where('status', Team::STATUS_PUBLISH)->find($team_ids);
                return datatables($teams ?? array())->addColumn('action', static function ($data) {
                    $html = "<a href='#' data-team_id='". $data->id ."' class='season-squad-btn dropdown-item'>Season Squad</a>";
                    return HtmlTemplatesHelper::get_action_dropdown($data, $html, false, false, false, false);
                })->addColumn('name', static function ($data) {
                    return $data->club->name;
                })->addColumn('image', static function ($data) {
                    return Helper::get_name_with_image('', $data->image);
                })->addColumn('players_count', static function ($data){
                    return $data->players->count();
                })->addColumn('selected_players_count', static function ($data) use ($id) {
                    return DB::table('player_season_team')->where('season_id', $id)->where('team_id', $data->id)->whereNotNull('player_id')->count();
                })->rawColumns(['image', 'action'])->make(true);
            }
        } catch (Exception $ex) {

        }
        $season = Season::query()->select('id', 'name', 'tournament_id')->findOrFail($id);
        $tournament = Tournament::query()->findOrFail($season->tournament_id);
        return view('pages.seasons.season_teams', ['title' => $season->name . ' Teams', 'season_id' => $id, 'item' => $season, 'breadcrumbs' => Breadcrumbs::generate('seasons.season_teams', $season, $tournament)]);
    }

    /**
     * @param Request $request
     * @param $season_id
     * @param $id = team_id
     * @return JsonResponse|mixed
     */
    public function get_season_team_squad(Request $request, $season_id, $id)
    {
        try {
            $player_roles = Sport::query()->select(['roles'])->first();
            $player_roles = json_decode($player_roles['roles'], true, 512, JSON_THROW_ON_ERROR);
            $player_roles = $player_roles['player_roles'];
            $team = Team::query()->find($id);
            $players = array();
            $temp_players = $team !== NULL ? $team->players()->get()->unique('pivot.player_id')->toArray() : array();
            $countries = Countries::getList();
            foreach ($temp_players as $player) {
                $player_in_squad = DB::table('player_season_team')->where('season_id', $season_id)->where('team_id', $id)->where('player_id', $player['id'])->count();
                $checked = '';
                if ($player_in_squad > 0) {
                    $checked = 'checked';
                }
                $players[] = array('id' => $player['id'], 'name' => $player['name'], 'image' => $player['image'], 'jersey' => $player['jersey'], 'country' => strtolower(array_search($player['country'], $countries, true)), 'playing_position' => $player_roles[$player['playing_position']], 'checked' => $checked);
            }
            return response()->json(array('team_name' => $team->club->name, 'players' => $players));
        } catch (Exception $ex) {
            return response()->json();
        }
    }

    /**
     * @param Request $request
     * @param $season_id
     * @param $id
     * @return JsonResponse
     */
    public function set_season_team_squad(Request $request, $season_id, $id)
    {
        try {
            if (!empty($request->all())) {
                DB::table('player_season_team')->where('season_id', $season_id)->where('team_id', $id)->delete();
                $players = $request->all();
                $players_added = 0;
                foreach ($players as $player => $on) {
                    $added = DB::table('player_season_team')->insert(['player_id' => $player, 'team_id' => $id, 'season_id' => $season_id]);
                    if ($added) {
                        $players_added++;
                    }
                }
                (new SeasonObserver())->dynamo_update(Season::query()->find($season_id));
                return Helper::jsonMessage($players_added > 0, 'seasons.season_teams', $players_added > 0 && $players_added === count($players) ? 'Season Squad Successfully Updated' : 'Unable to Update Season Squad Completely', ['id' => $season_id]);
            }
            return Helper::jsonMessage(true, NULL, 'No Player Selected, So Squad is Empty', ['id' => $season_id]);
        } catch( Exception $e ) {
        }
    }
}
