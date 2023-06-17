<?php

namespace App\Http\Controllers\ACL;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            if (request()->ajax()) {
                return datatables(Permission::all())->addColumn('actions', static function ($data) {
                    $buttons = '<div class="custom-control-inline">';
                    /*if (auth()->user()->role === Config::get('app.ROLE_ADMIN')) {*/
                    /*$buttons .= '<button title="edit" class="btn btn-icon rounded-circle btn-outline-primary mr-1 mb-1 waves-effect waves-light" onclick="window.location=' . "'" . route('permissions.edit', ['id' => $data->id]) . "'" . '"><i class="feather icon-edit-2"></i></button>';*/
                    $buttons .= '<button title="delete" class="btn btn-icon rounded-circle btn-outline-danger  mb-1 waves-effect waves-light destroy-item" id="' . $data->id . '"><i class="feather icon-trash"></i></button>';
                    /*}*/
                    $buttons .= '</div>';
                    return $buttons;
                })->rawColumns(['actions', 'displayStatus'])->make(true);
            }
        } catch (Exception $ex) {
        }
        return view('pages.acl.permissions.list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::all();
        return view('pages.acl.permissions.add', ['roles' => $roles, 'title' => 'Add Permission']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100',
            'permissions' => 'required'
        ]);
        $moduleName = $request['name'];
        $rolesArr = $request['roles'];
        $isErrorFound = false;
        $permissionsArr = array();
        $i = 0;
        foreach ($request['permissions'] as $permission) {
            $per = Permission::create(['name' => $moduleName . '.' . $permission]);
            if ($per == null) {
                $isErrorFound = true;
                break;
            }
            $permissionsArr[$i++] = $per;
        }
        if (isset($rolesArr)) {
            foreach ($rolesArr as $roleId) {
                $role = Role::findById($roleId);
                $role->syncPermissions($permissionsArr);
            }
        }
        return Helper::jsonMessage(!$isErrorFound, 'permissions', !$isErrorFound ? 'Permissions saved successfully' : 'Unable to save Permissions');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $permission = Permission::findOrFail($id)->delete();
            return Helper::jsonMessage($permission !== null, NULL, $permission !== null ? 'Record Successfully deleted' : 'Record not deleted');
        } catch (Exception $e) {

        }
    }
}
