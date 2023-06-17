<?php

namespace App\Http\Controllers;

use App\AdminLog;
use App\Helpers\HtmlTemplatesHelper;
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class AdminLogController extends BaseController
{
    public function index()
    {
        try {
            if (request()->ajax()) {
                return datatables(AdminLog::filters(request()))->addColumn('actions', function ($data) {
//                    $buttons = '<div class="custom-control-inline">';
//                    $buttons .= '<button title="View Admin" class="view-btn btn btn-icon rounded-circle btn-outline-success mr-1 mb-1 waves-effect waves-light" data-idval="' . $data->id . '"><i class="feather icon-eye"></i></button>';
//                    $buttons .= '</div>';
//                    return $buttons;
                    $html = "<a title='view' class='view-btn dropdown-item' data-item_id= '" . $data->id . "'>View Details</a>";
                    return HtmlTemplatesHelper::get_action_dropdown($data, $html, true, false, false, false,false,false);
                })->rawColumns(['actions', 'displayStatus'])->make(true);
            }
        } catch (Exception $ex){

        }
        return view('pages.admin-logs.list', ['breadcrumbs' => Breadcrumbs::generate('admin-logs')]);
    }

    /**
     * @inheritDoc
     */
    public function get(Request $request){
        $id = $request->input('id');
        $adminLog = AdminLog::query()->where('id', $id)->first();
        return response()->json($adminLog);
    }

    /**
     * @inheritDoc
     */
    public function create()
    {
        // TODO: Implement create() method.
    }

    /**
     * @inheritDoc
     */
    public function store(Request $request)
    {
        // TODO: Implement store() method.
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
        // TODO: Implement edit() method.
    }

    /**
     * @inheritDoc
     */
    public function update(Request $request, $id)
    {
        // TODO: Implement update() method.
    }

    /**
     * @inheritDoc
     */
    public function destroy($id)
    {
        // TODO: Implement destroy() method.
    }
}
