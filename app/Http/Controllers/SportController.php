<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Helpers\HtmlTemplatesHelper;
use App\Sport;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;

class SportController extends BaseController
{
    /**
     * @inheritDoc
     */
    public function index()
    {
        try {
            if (request()->ajax()) {
                return datatables(Sport::filters(request()))->addColumn('actions', static function($data){
                    return HtmlTemplatesHelper::get_action_dropdown($data, '', true,false, auth()->user()->can('Sports.Update'), auth()->user()->can('Sports.Delete'),false,false);
                })->rawColumns(['actions', 'status'])->make(true);
            }
        } catch (Exception $ex) {

        }
        $sports = Sport::query()->latest()->paginate(Config::get('app.itemPerPage'));
        return view('pages.sports.list',['sports' => $sports]);
    }

    /**
     * @inheritDoc
     */
    public function create()
    {
        return view('pages.sports.add', ['title' => 'Add Sport']);
    }

    /**
     * @inheritDoc
     * @throws \Throwable
     */
    public function store(Request $request)
    {
        try {
            request()->validate(Sport::$validation);
            $sport = new Sport;
            $sport->fill($request->all());
            if ($sport->sport_name === NULL) {
                $sport->sport_name = $sport->name;
            }
            $sport->saveOrFail();
        } catch (Exception $e) {
            dd($e->getMessage());
        }
        return Helper::jsonMessage($sport->id !== null, Sport::INDEX_URL);
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
    public function edit($id)
    {
        $sport = Sport::query()->findOrFail($id);
        return view('pages.sports.add', ['title' => 'Edit Sport', 'item' => $sport]);
    }

    /**
     * @inheritDoc
     */
    public function update(Request $request, $id)
    {
        request()->validate(Sport::$validation);
        $sport = Sport::query()->findOrFail($id);
        $sport->fill($request->all());
        if ($sport->sport_name === NULL) {
            $sport->sport_name = $sport->name;
        }
        $sport->update();
        return Helper::jsonMessage($sport !== null, Sport::INDEX_URL, $sport !== null ? 'Record Successfully Updated' : 'Unable to update');
    }

    /**
     * @inheritDoc
     */
    public function destroy($id)
    {
        $model = Sport::query()->findOrFail($id);
        if($model->stadiumFacilities()->count()> 0){
            return Helper::jsonMessage(false, NULL,  "Can't delete, Sport is being used ".$model->name);
        }
        try {
            $model->delete();
            return Helper::jsonMessage(true, NULL, 'Record Successfully deleted');
        } catch (\Exception $e) {
            return Helper::jsonMessage(false, NULL,  $e->getMessage());
        }
    }
}
