<?php

namespace App\Http\Controllers\ACL;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    public static function isPermissionSelected($permissionsArr, $permissionString)
    {
        foreach ($permissionsArr as $permission) {
            if ($permission->name === $permissionString) {
                return true;
            }
        }
        return false;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            if (request()->ajax()) {
                return datatables(Role::with('permissions')->get())->addColumn('permissions', static function ($role) {
                    $spans = '<div class="custom-control-inline">';
                    $i = 0;
                    foreach ($role->permissions as $permission) {
                        if ($i < 5) {
                            $spans .= '<span class="ml-1 bg-light">' . $permission->name . '</span>';
                        }
                        $i++;
                    }
                    $spans .= '</div>';
                    //$anchor = '<a href="javascript:void(0);" class="font-weight-bold text-black-50" data-trigger="click" data-container="body" data-toggle="popover" data-html="true" data-placement="auto" data-content="'. $spans .'"><i class="fa fa-plus-circle mr-1"></i>'. $i.'</a>';
                    $anchor = '<a href="javascript:void(0);" class="font-weight-bold text-black-50" title="Popover Header" data-trigger="click" data-container="body" data-toggle="popover" data-html="true" data-placement="auto" data-content="This is sample pop over content."><i class="fa fa-plus-circle"></i>' . $i . '</a>';
                    //$anchor = '<a href="#" data-toggle="popover" title="Popover Header" data-content="Some content inside the popover">Toggle popover</a>';

                    return $spans . '<br>' . $anchor;
                    //return $spans;
                })->addColumn('actions', static function ($data) {
                    $buttons = '<div class="custom-control-inline">';

                    if (auth()->user()->can('Roles.Update')) {
                        $buttons .= '<button title="edit" class="btn btn-icon rounded-circle btn-outline-primary mr-1 mb-1 waves-effect waves-light" onclick="window.location=' . "'" . route('roles.edit', ['id' => $data->id]) . "'" . '"><i class="feather icon-edit-2"></i></button>';
                    }
                    if ($data->id != 1 &&  auth()->user()->can('Roles.Delete')) {
                        $buttons .= '<button title="delete" class="btn btn-icon rounded-circle btn-outline-danger  mb-1 waves-effect waves-light destroy-item" id="' . $data->id . '"><i class="feather icon-trash"></i></button>';
                    }
                    /*}*/
                    $buttons .= '</div>';
                    return $buttons;
                })->rawColumns(['actions', 'permissions'])->make(true);
            }
        } catch (Exception $ex) {
        }
        return view('pages.acl.roles.list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pages.acl.roles.add', ['title' => 'Add Role']);
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
            'name' => 'required|max:50',
            'permissions' => 'required'
        ]);
        $roleName = $request['name'];
        $permissions = $request['permissions'];
        $role = Role::create(['name' => $roleName]);
        $role->syncPermissions($permissions);
        return Helper::jsonMessage($role !== null, 'roles', $role !== null ? 'Role saved successfully' : 'Unable to save Role');
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
        $role = Role::findById($id);
        return view('pages.acl.roles.add', ['title' => 'Edit Role', 'item' => $role]);
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
        $request->validate([
            'name' => 'required|max:50',
            'permissions' => 'required'
        ]);
        $permissions = $request['permissions'];

        $role = Role::findById($id);
        $role->syncPermissions($permissions);
        return Helper::jsonMessage($role !== null, 'roles', $role !== null ? 'Record updated successfully' : 'Unable to update record');
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
            $role = Role::findOrFail($id)->delete();
            return Helper::jsonMessage($role !== null, NULL, $role !== null ? 'Record Successfully deleted' : 'Record not deleted');
        } catch (Exception $e) {

        }
    }

}
