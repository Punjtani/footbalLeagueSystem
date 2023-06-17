<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Helpers\HtmlTemplatesHelper;
use App\Sport;
use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use App\Tenant;


class TenantController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        try {
            if (request()->ajax()) {
                return datatables(Tenant::filters(request()))->addColumn('actions', static function ($data) {
                    $html = '<a title="View Keys" class="view-keys-btn dropdown-item" data-api="' . $data->api_token . '" data-url="' . route('tenants.credentialchange', ['tenant' => $data->id]) . '">View Keys</a>';
                    return HtmlTemplatesHelper::get_action_dropdown($data, $html, true);
                })->rawColumns(['actions', 'status'])->make(true);
            }
        } catch (Exception $ex) {
        }
        return view('pages.tenants.list');
    }

    public function create()
    {
//        $sports = Sport::query()->where('status', array_search('Active', Config::get('custom.status'), true))->get();
//        return view('pages.tenants.add', ['title' => 'Add Tenant', 'sports' => $sports]);
    }

    /**
     * @inheritDoc
     */
    public function store(Request $request)
    {
        $test_email = User::query()->where('email', strtolower($request->input('email')))->first();
        if ($test_email === NULL) {
            $request->validate(Tenant::$validation);
            $tenant = new Tenant;
            $tenant->fill($request->all());
            $tenant->api_token = Str::random(32);
            $tenant->save();
        }
        return Helper::jsonMessage($tenant->id !== null, Tenant::INDEX_URL, $tenant !== null ? 'Record Successfully Created' : 'Tenant With same Email Already Exists');
    }

    public function get(Request $request)
    {
        $id = $request->input('id');
        $tenant = Tenant::query()->find($id);
        return response()->json($tenant);
    }

    /**
     * @inheritDoc
     */
    public function edit($id)
    {
        $tenant = Tenant::query()->findOrFail($id);
        $sports = Sport::query()->where('status', Sport::STATUS_PUBLISH)->get();
        return view('pages.tenants.add', ['title' => 'Edit Tenant', 'sports' => $sports, 'item' => $tenant]);
    }

    public function update(Request $request, $id)
    {
        $test_email = User::query()->where('email', strtolower($request->input('email')))->where('id', '<>', $id)->first();
        if ($test_email === NULL) {
            $request->validate(Tenant::$validation);
            $tenant = Tenant::query()->find($request->input('id'));
            $tenant->name = $request->input('name');
            $tenant->sport_id = $request->input('sport_id');
            $tenant->status = $request->input('status');
            $tenant->save();
        }
        return Helper::jsonMessage($tenant !== null, Tenant::INDEX_URL, $tenant !== null ? 'Record Successfully Updated' : 'Tenant With same Email Already Exists');
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
    public function destroy($id)
    {
        try {
            $tenant = Tenant::findOrFail($id)->delete();
            return Helper::jsonMessage($tenant !== null, NULL,  $tenant !== NULL ? 'Record Successfully deleted' : 'Unable to delete record');
        } catch (Exception $e) {
        }
    }

    public function credentialChange(Tenant $tenant)
    {
        $tenant->api_token = Str::random(32);
        $tenant->save();
        return redirect()->back();
    }
}
