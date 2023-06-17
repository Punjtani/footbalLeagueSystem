<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class isSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (!auth()->user()->hasRole('SuperAdmin')) {
            return redirect()->route('dashboard');
        }
        return $next($request);
    }
}
