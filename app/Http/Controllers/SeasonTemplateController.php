<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Helpers\HtmlTemplatesHelper;
use App\SeasonTemplate;
use App\Team;
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class SeasonTemplateController extends BaseController
{
    public function index()
    {
        try {
            if (request()->ajax()) {
                return datatables(SeasonTemplate::filters(request()))->addColumn('actions', static function($data) {
                    return HtmlTemplatesHelper::get_action_dropdown($data);
                })->rawColumns(['actions', 'status'])->make(true);
            }
        } catch (Exception $ex){
        }
        return view('pages.season-templates.list', ['add_url' => route('season-templates.create'), 'button_text' => 'Add Template', 'advance_filters' => $this->advance_filters(), 'breadcrumbs' => Breadcrumbs::generate('season_templates')]);
    }

    private function advance_filters(): array
    {
        return array(
            'Number Of Teams' => array('type' => 'number', 'sub_type' => '', 'placeholder' => 'Number of Teams', 'name' => 'number_of_teams', 'data' => []),
            'Teams Greater Than' => array('type' => 'number', 'sub_type' => 'range_after', 'placeholder' => 'Number of Teams Greater Than', 'name' => 'number_of_teams', 'data' => []),
            'Teams Less Than' => array('type' => 'number', 'sub_type' => 'range_before', 'placeholder' => 'Number of Teams Less Than', 'name' => 'number_of_teams', 'data' => []),
            'Template type' => array('type' => 'dropdown', 'sub_type' => '', 'placeholder' => 'Select Template Type', 'name' => 'type', 'data' => Config::get('custom.template_types')),
        );
    }

    public function create()
    {
        $team_count_tournament_0 = Team::query()->where('status', Team::STATUS_PUBLISH)->count();
        $template_types = Config::get('custom.template_types');
        return view('pages.season-templates.add', ['title' => "Add Season's Template", 'template_types' => $template_types, 'teams_count' => $team_count_tournament_0, 'breadcrumbs' => Breadcrumbs::generate('season_templates.create')]);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $template = new SeasonTemplate();
        $template->fill($request->all());
        if ($request->has('type')) {
            $stages = $request->input('number_of_stages') ?? 0;
            $configuration = array();
            for ($i=1 ; $i <= $stages ; $i++) {
                $home_away = $request->input('stage_' . $i . '_home_and_away');
                $configuration['stage_' . $i] = array(
                    'name' => $request->input('stage_' . $i . '_name'),
                    'type' => $request->input('stage_' . $i . '_type'),
                    'home_and_away' => $home_away !== NULL && $home_away === 'on' ? '1' : '0',
                );
                if ($request->has('round_robin_type_stage_' . $i)) {
                    $round_robin_type = $request->input('round_robin_type_stage_' . $i);
                    $configuration['stage_' . $i]['round_robin_type'] = $round_robin_type !== 0 && $round_robin_type !== '0' ? $round_robin_type : $request->input('multiple_round_robin_value_stage_' . $i);
                    $configuration['stage_' . $i]['number_of_groups'] = $request->input('number_of_groups');
                    $configuration['stage_' . $i]['number_of_teams_forward'] = $request->input('number_of_teams_forward');
                }
            }
            try {
                $template->configuration = json_encode($configuration, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
            }
        }
        $template->save();
        return Helper::jsonMessage($template->id !== null, SeasonTemplate::INDEX_URL);
    }

    public function edit($id)
    {
        $team_count_tournament_0 = Team::query()->where('status', Team::STATUS_PUBLISH)->count();
        $template_types = Config::get('custom.template_types');
        $template = SeasonTemplate::query()->findOrFail($id);
        $configuration = $template->configuration;
        $template->number_of_groups = $configuration['stage_1']['number_of_groups'] ?? NULL;
        $template->number_of_teams_forward = $configuration['stage_1']['number_of_teams_forward'] ?? NULL;
        return view('pages.season-templates.add', ['title' => "Edit Season's Template", 'item' => $template, 'template_types' => $template_types, 'teams_count' => $team_count_tournament_0, 'breadcrumbs' => Breadcrumbs::generate('season_templates.edit', $template)]);
    }

    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $template = SeasonTemplate::query()->findOrFail($id);
        $template->fill($request->all());
        if ($request->has('type')) {
            $stages = $request->input('number_of_stages') ?? 0;
            $configuration = array();
            for ($i=1 ; $i <= $stages ; $i++) {
                $home_away = $request->input('stage_' . $i . '_home_and_away');
                $configuration['stage_' . $i] = array(
                    'name' => $request->input('stage_' . $i . '_name'),
                    'type' => $request->input('stage_' . $i . '_type'),
                    'home_and_away' => $home_away !== NULL && $home_away === 'on' ? '1' : '0',
                );
                if ($request->has('round_robin_type_stage_' . $i)) {
                    $round_robin_type = $request->input('round_robin_type_stage_' . $i);
                    $configuration['stage_' . $i]['round_robin_type'] = $round_robin_type !== 0 ? $round_robin_type : $request->input('multiple_round_robin_value_stage_' . $i);
                    $configuration['stage_' . $i]['number_of_groups'] = $request->input('number_of_groups');
                    $configuration['stage_' . $i]['number_of_teams_forward'] = $request->input('number_of_teams_forward');
                }
            }
            try {
                $template->configuration = json_encode($configuration, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
            }
        }
        $template->save();
        return Helper::jsonMessage($template !== null, SeasonTemplate::INDEX_URL, $template !== null ? 'Record Successfully Updated' : 'Unable to Update');
    }

    /**
     * @param $id
     * @return array
     */
    public function get($id): array
    {
        try {
            $template = SeasonTemplate::query()->findOrFail($id);
            return $template->getAttributes();
        } catch (Exception $e) {
        }
        return [];
    }

    public function destroy($id): ?\Illuminate\Http\JsonResponse
    {
        try {
            $template = SeasonTemplate::query()->findOrFail($id)->delete();
            return Helper::jsonMessage($template !== null, NULL,  $template !== NULL ? 'Record Successfully deleted' : 'Unable to delete record');
        } catch (Exception $e) {
        }
    }
}
