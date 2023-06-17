<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Helpers\HtmlTemplatesHelper;
use App\Sponsor;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;

class SponsorController extends BaseController
{
    /**
     * @inheritDoc
     */
    public function index()
    {
        try {

            if (request()->ajax()) {
                return datatables(Sponsor::filters(request()))->addColumn('actions', static function($data){
                    return HtmlTemplatesHelper::get_action_dropdown($data, '', true,false,auth()->user()->can('Sponsors.Create'), auth()->user()->can('Sponsors.Delete'),false,false);
                })->addColumn('image',static function($data){
                        return  Helper::get_name_with_image('', $data->image);
                })->rawColumns(['image','actions', 'status'])->make(true);
            }
        } catch (Exception $ex) {

        }
        $sports = Sponsor::query()->latest()->paginate(Config::get('app.itemPerPage'));
        return view('pages.sponsors.list',['sports' => $sports]);
    }

    /**
     * @inheritDoc
     */
    public function create()
    {
        return view('pages.sponsors.add', ['title' => 'Add Sport']);
    }

    /**
     * @inheritDoc
     * @throws \Throwable
     */
    public function store(Request $request)
    {
        $sponsor = new Sponsor;
        try {
            request()->validate(Sponsor::$validation);
            $sponsor->fill($request->all());
            $image = '';
            if ($request->has('image')) {
                $image = $request->file('image');
            }
            $request->request->add(['image' => $image]);
            $sponsor->saveOrFail();
        } catch (Exception $e) {

        }
        return Helper::jsonMessage($sponsor->id !== null, Sponsor::INDEX_URL);
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
        $sponsor = Sponsor::query()->findOrFail($id);
        return view('pages.sponsors.add', ['title' => 'Edit Sponsor', 'item' => $sponsor]);
    }

    /**
     * @inheritDoc
     */
    public function update(Request $request, $id)
    {
        request()->validate(Sponsor::$validation);
        $sponsor = Sponsor::query()->findOrFail($id);
        $sponsor->fill($request->all());
        $sponsor->update();
        return Helper::jsonMessage($sponsor !== null, Sponsor::INDEX_URL, $sponsor !== null ? 'Record Successfully Updated' : 'Unable to update');
    }

    /**
     * @inheritDoc
     */
    public function destroy($id)
    {
        try {
            $sponsor = Sponsor::query()->findOrFail($id)->delete();
            return Helper::jsonMessage($sponsor !== null, NULL,$sponsor !== NULL ? 'Record Successfully deleted' : 'Unable to delete record');
        } catch (Exception $e) {
        }
    }
}
