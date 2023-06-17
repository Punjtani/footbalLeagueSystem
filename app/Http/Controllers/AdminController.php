<?php

namespace App\Http\Controllers;

use App\Helpers\HtmlTemplatesHelper;
use App\Mail\UserForgotPassword;
use App\Mail\UserInvitation;
use App\Tenant;
use App\User;
use Carbon\Carbon;
use Exception;use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Helpers\Helper;
use App\Stadium;
use App\StadiumFacility;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class AdminController extends BaseController
{
    public function index()
    {

        try {
            if (request()->ajax()) {
                return datatables(User::filters(request())->where('user_type','!=','user')->with(['roles','stadium'])->where('id','!=',1))->addColumn('actions', static function ($data) {

                    $html = '';
                    if(request()->user()->can('Users.Update')){
                        $html = '<a class="reset-password-btn dropdown-item" data-url="' . route('admins.reset_password', ['admin' => $data->id]) . '">Reset Password</a>';
//                        $html .= '<a class="view-keys-btn dropdown-item" data-token="' . $data->login_token . '" data-url="' . route('admins.credentialchange', ['admin' => $data->id]) . '">Login Keys</a>';
                    }
                    return HtmlTemplatesHelper::get_action_dropdown($data, $html, true,false, request()->user()->can('Users.Update'), false,false,false);
                })
                ->addColumn('role', function($data){
                    $roles = $data->roles;
                    if(empty($roles[0])){
                        return "No Role Assigned";
                    }

                    if($roles[0]->id === 2){
                        $stadiumLabel = "All Locations";
                        if(!empty($data->stadium)){
                            $stadiumLabel = $data->stadium->name;
                        }
                        return 'Admin @ '.$stadiumLabel;
                    }

                    return $roles[0]->name;
                })
                ->rawColumns(['actions', 'displayStatus'])->make(true);
            }
        } catch (Exception $ex){

        }
        return view('pages.admins.list', ['add_url' => route('admins.create'), 'advance_filters' => $this->advance_filters(), 'breadcrumbs' => Breadcrumbs::generate('admins')]);
    }

    /*
     *
     *
     */
    private function advance_filters(): array
    {
        return array(
            'Admin Type' => array('type' => 'dropdown', 'sub_type' => '', 'placeholder' => 'Select Admin Type', 'name' => 'role', 'data' => array('superadmin' => 'Super Admin')),
            'Email Address' => array('type' => 'text', 'sub_type' => 'like', 'placeholder' => 'Search Email', 'name' => 'email', 'data' => array()),
        );
    }


    public function create()
    {
        $tenants = Tenant::query()->where('status', Tenant::STATUS_PUBLISH)->get();
        $stadiums = Stadium::where('status', 1)->pluck('name','id');
        return view('pages.admins.add', ['title' => 'Add Admin', 'tenants' => $tenants,'stadiums'=> $stadiums, 'breadcrumbs' => Breadcrumbs::generate('admins.create')]);
    }

    public function store(Request $request)
    {
        $tenantStatus = $this->check_tenant_status($request);
        if ($tenantStatus !== true) {
            return $tenantStatus;
        }
        $request->merge(['email' => strtolower($request->input('email'))]);
        $rules = User::$validation;
        $customMessages = array();
        if ($request->input('role') === 'tenant') {
            $rules['tenant_id'] = 'required';
            $customMessages['tenant_id'] = 'Tenant Field is Required';
        }
        $request->validate($rules, $customMessages);
        $email = $request->input('email');

        $checkUser = User::query()->where('email', 'ILIKE', $email)->first();
        if ($checkUser !== NULL) { // non unique user, throw error
            return Helper::jsonMessage(false, null, 'Email already exists');
        } else {
            $request->merge(['email' => strtolower($request->input('email'))]);
            $rules = User::$validation;
            $customMessages = array();
            if ($request->input('role') === 'tenant') {
                $rules['tenant_id'] = 'required';
                $customMessages['tenant_id'] = 'Tenant Field is Required';
            }
            $request->validate($rules, $customMessages);
            $str = Str::random();
            $password = Hash::make($str);
            $request->request->add(['password' => $password, 'password_original' => $str]);
            $user = new User;
            $user->fill($request->all());
            $user->password = $password;
            $user->email_verified_at = Carbon::now();
            $user->login_token = Str::random(32);
            $user->save();
            $role = Role::findById($request->role);
            $user->assignRole($role);
            return Helper::jsonMessage($user->id !== null, User::INDEX_URL);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function get(Request $request): JsonResponse
    {
        $tenant = User::query()->findOrFail($request->input('id'));
        return response()->json($tenant);
    }

    public function edit($id)
    {
        $admin = User::query()->findOrFail($id);
        $tenants = Tenant::query()->where('status', Tenant::STATUS_PUBLISH)->get();
        $stadiums = Stadium::where('status', 1)->pluck('name','id');
        return view('pages.admins.add', ['title' => 'Edit Admin', 'tenants' => $tenants, 'stadiums'=>$stadiums, 'item' => $admin, 'breadcrumbs' => Breadcrumbs::generate('admins.edit', $admin)]);
    }

    public function update(Request $request, $id)
    {
        $test_email = User::query()->where('email', strtolower($request->input('email')))->where('id', '<>', $id)->first();
        if ($test_email === NULL) {
            $tenantStatus = $this->check_tenant_status($request);
            if ($tenantStatus !== true) {
                return $tenantStatus;
            }
            $rules = User::$validation;
            unset($rules['email']);
            $customMessages = array();
            if ($request->input('role') === 'tenant') {
                $rules['tenant_id'] = 'required';
                $customMessages['tenant_id'] = 'Tenant Field is Required';
            }
            $request->validate($rules, $customMessages);

            $user = User::query()->findOrFail($request->input('id'));
            $user->fill($request->all());
            if ($request->input('role') !== 'tenant') {
                $user->tenant_id = null;
            }
            $user->save();

            $role = Role::findById($request->role);
            $user->syncRoles($role);
        }
        return Helper::jsonMessage($user !== null, User::INDEX_URL, $user !== null ? 'Record Successfully Updated' : 'Unable to update');
    }

    /**
     * @param Request $request
     * @return bool|JsonResponse
     */
    private function check_tenant_status(Request $request)
    {
        if ($request->input('role') === 'tenant' && $request->input('status') === array_search('Active', Config::get('custom.status'), true)) {
            $tenant = Tenant::findOrFail($request->input('tenant'));
            if ($tenant->getRawOriginal('status') !== array_search('Active', Config::get('custom.status'), true)) {
                return Helper::jsonMessage(false,NULL,  $tenant->name . ' Tenant is not active, You cannot active the Admin');
            }
        }
        return true;
    }

    public function destroy($id)
    {
        try {
            User::query()->findOrFail($id)->update(['status' => User::STATUS_DRAFT]);
            $user = User::query()->findOrFail($id)->delete();
            return Helper::jsonMessage($user !== null, NULL, $user !== null ? 'Record Successfully deleted' : 'Record not deleted');
        } catch (Exception $e) {
        }
    }

    /**
     * @return Application|Factory|View
     */
    public function getProfile()
    {
        $tenant = User::query()->findOrFail(auth()->user()->id);
        return view('pages.admins.editProfile', ['admin' => $tenant,'breadcrumbs' => Breadcrumbs::generate('admins.profile')]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $message['type'] = 'error';
        if ($request->has('id')) {
            $user = User::query()->where('id', $request->input('id'))->first();
            if ($user !== NULL) {
                if ($request->has('name'))
                    $user->name = $request->input('name');
                if ($request->has('current_password') && $request->has('new_password') && $request->has('confirm_new_password')) {
                    if (! Hash::check($request->input('current_password'), $user->password)) {
                        $message['message'] = 'Incorrect Current Password';
                        $message['close'] = 'false';
                        return response()->json($message);
                    }

                    if ($request->input('new_password') !== $request->input('confirm_new_password')) {
                        $message['message'] = 'New Password did not matched Confirm New Password';
                        $message['close'] = 'false';
                        return response()->json($message);
                    }

                    $user->password = Hash::make($request->input('new_password'));
                }
            }
            $user->save();
        }
        return Helper::jsonMessage($user !== NULL, NULL,$user !== NULL ? 'Record Updated Successfully' : 'Unable to update');
    }

    /**
     * @param $id
     */
    public function show($id)
    {
        // TODO: Implement show() method.
    }

    /**
     * @param User $admin
     * @return RedirectResponse
     */
    public function credentialChange(User $admin)
    {
        $admin->login_token = Str::random(32);
        $admin->save();
        return redirect()->back();
    }

    /**
     * @param User $admin
     * @return JsonResponse
     */
    public function reset_password(User $admin)
    {
        try {
            $password = Str::random();
            $admin->password = $password;
            Mail::to($admin->email)->send(new UserForgotPassword($admin));
            $admin->password = Hash::make($password);
            $admin->save();
            $success = true;
        } catch (Exception $ex) {
            $success = false;
        }
        return Helper::jsonMessage($success, NULL,$success ? 'Password Reset Email Sent' : 'Unable to Reset Password');
    }
}
