<?php

namespace App\Http\Middleware;

use App\AdminLog;
use App\Sport;
use App\Tenant;
use App\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Jenssegers\Mongodb\Eloquent\Builder;
use Jenssegers\Mongodb\Eloquent\Model;

class belongsToTenantMiddleware
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
//        if (auth()->check() && auth()->user()->tenant_id !== NULL) {
//            $request->merge(['tenant_id' => auth()->user()->tenant_id]);
//            $request->merge(['isTenant' => auth()->user()->role == Config::get('app.ROLE_ADMIN') ? 'false' : 'true']);
//            $request->merge(['model_name' => Model::class]);
//        }
        return $next($request);
    }
}
