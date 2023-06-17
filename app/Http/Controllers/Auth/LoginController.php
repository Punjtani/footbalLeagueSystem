<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Sport;
use App\Tenant;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('guest')->except('logout');
    }

    /**
     * Login with user token.
     *
     * @param $token
     * @return RedirectResponse|Redirector
     */
    public function loginWithToken($token)
    {
        if (!empty($token)) {
            $user = User::query()->where('token', $token)->where('status', User::STATUS_PUBLISH)->where('isDefault', true)->first();
            if ($user !== NULL) {
                Auth::loginUsingId($user->id);
            }
        }
        return redirect(route($this->redirectTo));
    }

    protected function credentials(Request $request)
    {
        return array_merge($request->only($this->username(), 'password'), ['status' => User::STATUS_PUBLISH]);
    }

//    protected function authenticated(Request $request, User $user)
//    {
//        // put your thing in here
//        if (Cache::has('sport')) {
//            Cache::forget('sport');
//        }
//        if (Cache::has('tenant')) {
//            Cache::forget('tenant');
//        }
//        if ($user->role === Config::get('app.ROLE_ADMIN')) {
//            $tenant = Tenant::query()->find($user->tenant_id);
//            Cache::forever('tenant', $tenant);
//            Cache::forever('sport', Sport::query()->find($tenant->sport_id));
//        }
//        return redirect()->intended($this->redirectPath());
//    }

    /**
     * Get the failed login response instance.
     *
     * @param Request $request
     * @return mixed
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        $errors = [$this->username() => trans('User does not exist')];

        // Load user from database
        $user = User::query()->where($this->username(), $request->{$this->username()})->first();

        // Check if user was successfully loaded, that the password matches
        // and active is not 1. If so, override the default error message.
        if ($user && Hash::check($request->password, $user->password) && $user->getRawOriginal('status') != User::STATUS_PUBLISH) {
            $errors = [$this->username() => trans('User not active, Contact Administrator')];
        }

        if ($request->expectsJson()) {
            return response()->text($errors, 422);
        }
        return redirect()->back()
            ->withInput($request->only($this->username(), 'remember'))
            ->withErrors($errors);
    }
}
