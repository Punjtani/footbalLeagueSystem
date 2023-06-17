<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->user()->getRawOriginal('status') !== User::STATUS_PUBLISH) {
            auth()->logout();
//            Session::put('status', 'Your account is not activated yet, contact your Administrator');
            return redirect()->route('landing')->with([
                'flash_status' => 'warning',
                'flash_message' => 'Your account is not activated yet, contact your Administrator'
            ]);
        } elseif (!auth()->user()->hasRole('User')) {
            auth()->logout();
            return redirect()->route('landing')->with([
                'flash_status' => 'error',
                'flash_message' => 'You are unauthorized for this request.'
            ]);
        }
        return $next($request);
    }
}
