<?php

namespace App\Http\Controllers;

use App\Association;
use App\BookingRule;
use App\Match;
use App\Sport;
use App\Helpers\HtmlTemplatesHelper;
use Carbon\Carbon;
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;
use Illuminate\Http\Request;
use App\Tournament;
use App\Tenant;
use App\Helpers\Helper;
use Exception;
use Illuminate\Support\Facades\Config;

class TournamentController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        try {
            if (request()->ajax()) {
                $associations = Association::all();
                return datatables(Tournament::filters(request()))->addColumn('actions', static function ($data) {
                    $html = '<a class="dropdown-item" onclick="window.location=' . "'" . route('tournaments.league-table.create', ['tournament_id' => $data->id]) . "'" . '">Add League Table</a>';

                    return HtmlTemplatesHelper::get_action_dropdown($data, $html, false, auth()->user()->can('Leagues.Read'), auth()->user()->can('Leagues.Update'), auth()->user()->can('Leagues.Delete'),false,false);
                })->addColumn('image', static function ($data) {
                    return Helper::get_name_with_image('', $data->image);
                })->editColumn('hide_frontend', function ($data) {
                    return $data->hide_frontend ? "Yes" : "No";
                })->rawColumns(['image', 'actions', 'status'])->make(true);
            }
        } catch (Exception $ex) {
        }
        return view('pages.tournaments.list', ['add_url' => route('tournaments.create'), 'advance_filters' => $this->advance_filters(), 'breadcrumbs' => Breadcrumbs::generate('tournaments')]);
    }

    public function getSpecificTournaments($type){
           $tournament = Tournament::where('status',1)->where('booking_type',$type)->pluck('name','id');
          return response()->json(['tournament' => $tournament]);
    }

    private function advance_filters(): array
    {
        return array();
    }

    /**
     * @inheritDoc
     */
    public function create()
    {
        $associations = Association::query()->where('status', Association::STATUS_PUBLISH)->get();
        $team_groups = Sport::query()->select(['groups'])->first();
        $team_groups = json_decode($team_groups['groups'], true, 512, JSON_THROW_ON_ERROR);
        $team_groups = $team_groups['team_group'];
        return view('pages.tournaments.add', ['title' => 'Add League', 'associations' => $associations, 'team_group' => $team_groups, 'breadcrumbs' => Breadcrumbs::generate('tournaments.create')]);
    }

    public function store(Request $request)
    {
    //    dd($request->all());
        $rules = Tournament::$validation;
        $validation_rules = $this->validate_lang_tabs();
        $rules = array_merge($rules, $validation_rules['rules']);
        request()->validate($rules, $validation_rules['customMessages']);
        $image = '';
        if ($request->has('image')) {
            $image = $request->file('image');
        }
        $request->request->add(['image' => $image]);
        $tournament = new Tournament;
//        $tournament->name = $request->name_en;
//        $tournament->description = $request->description_en;
        $tournament->fill(request()->all());
        $tournament->team_group = '[]';
        $tournament->save();
        return Helper::jsonMessage($tournament->id !== null, Tournament::INDEX_URL);
    }

    /**
     * @inheritDoc
     */
    public function show($id)
    {

        $tournament = Tournament::query()->findOrFail($id);
        try {
            if (request()->ajax()) {

                $query = Match::with(['booking', 'booking.club1', 'booking.club2', 'booking.stadium', 'booking.stadiumFacility'])->whereHas('booking', function ($query) use ($tournament) {
                    $query->where('tournament_id', $tournament->id);
                });
                return datatables($query)
                    ->editColumn('youtube_link', function ($data) {
                        if (!empty($data->youtube_link)) {
                            return "<a target='_blank' href='{$data->youtube_link}'><span class='btn btn-icon btn-flat-primary'><i class='lab la-youtube'></i></span></a>";
                        }
                    })
                    ->editColumn("booking.booking_date", function ($data) {
                        return Carbon::createFromFormat("Y-m-d H:i:s", $data->booking->booking_date . " " . $data->booking->start_time)->format('d/m/Y H:i');
                    })->addColumn('score', function ($data) {
                        $club1Name = empty($data->booking->club1) ? "" : $data->booking->club1->name;
                        $club2Name = empty($data->booking->club2) ? "" : $data->booking->club2->name;

                        return "<span class='font-small-1'>{$club1Name}</span> <strong>{$data->team_1_score} : {$data->team_2_score}</strong> <span class='font-small-1'>{$club2Name}</span> ";
                    })
                    ->editColumn('booking.stadium.name', function ($data) {
                        $stadiumName = empty($data->booking->stadium) ? "" : $data->booking->stadium->name;
                        $facilityName = empty($data->booking->stadiumFacility) ? "" : $data->booking->stadiumFacility->name;
                        return $stadiumName . " " . $facilityName;
                    })
                    ->addColumn('actions', static function ($data) {
                        $actions [] = "<a onclick='editMatch($data->id)' class='btn btn-flat-secondary'><i class='feather icon-edit'></i></a>";
                        $actions [] = "<a onclick='deleteMatch($data->id)' class='btn btn-flat-secondary'><i class='feather icon-trash'></i></a>";
                        return join(' | ', $actions);
                    })->rawColumns(['actions', 'score', 'youtube_link'])->make(true);

            }
        } catch (Exception $ex) {
        }

        return view('pages.tournaments.show', ['title' => 'League: ' . $tournament->name, 'item' => $tournament, 'breadcrumbs' => Breadcrumbs::generate('tournaments.edit', $tournament)]);
    }

    /**
     * @inheritDoc
     */
    public function edit($id)
    {
        $tournament = Tournament::query()->findOrFail($id);
        $associations = Association::query()->where('status', Association::STATUS_PUBLISH)->get();
        $team_groups = Sport::query()->select(['groups'])->first();
        $team_groups = json_decode($team_groups['groups'], true, 512, JSON_THROW_ON_ERROR);
        $team_groups = $team_groups['team_group'];
        return view('pages.tournaments.add', ['title' => 'Edit League', 'associations' => $associations, 'item' => $tournament, 'team_group' => $team_groups, 'breadcrumbs' => Breadcrumbs::generate('tournaments.edit', $tournament)]);
    }

    public function update(Request $request, $id)
    {
         $rules = Tournament::$validation;
         unset($rules['image']);
         $validation_rules = $this->validate_lang_tabs();
         $rules = array_merge($rules, $validation_rules['rules']);
         request()->validate($rules, $validation_rules['customMessages']);
         $tournament = Tournament::query()->findOrFail($id);
         if (request()->has('image')) {
             request()->request->add(['image' => request()->file('image')]);
         }
        if (!empty($request->hide_frontend)) {
            $tournament->hide_frontend = true;
        } else {
            $tournament->hide_frontend = false;
        }
        $tournament->fill($request->all());
        $tournament->team_group = '[]';
        $tournament->save();
        return Helper::jsonMessage($tournament !== null, Tournament::INDEX_URL, $tournament !== null ? 'Record Successfully Updated' : 'Unable to Update');
    }

    /**
     * @inheritDoc
     */
    public function destroy($id)
    {
        $tournament = Tournament::query()->findOrFail($id);
        if ($tournament->bookings()->count() > 0) {
            return Helper::jsonMessage(false, NULL, "Can't delete, Booking exists for this League.");
        }
        try {
            $tournament = Tournament::query()->findOrFail($id)->delete();
            return Helper::jsonMessage($tournament !== null, NULL, $tournament !== NULL ? 'Record Successfully deleted' : 'Unable to delete record');
        } catch (Exception $e) {
            return Helper::jsonMessage(false, NULL, $e->getMessage());
        }
    }

    public function matchShow($id)
    {

        $match = Match::whereId($id)->with(['booking', 'booking.club1', 'booking.club2'])->first();
        return [
            "data" => $match,
        ];
    }

    public function matchUpdate($id, Request $request)
    {
        $match = Match::findOrFail($id);
        $match->fill($request->all());
        $match->save();
        return [
            'success' => true,
        ];
    }

    public function destroyMatch($id, Request $request)
    {
        $match = Match::findOrFail($id);
        $match->delete();
        return [
            'success' => true,
        ];
    }
}
