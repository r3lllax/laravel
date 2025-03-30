<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use Closure;
use Illuminate\Http\Request;

class UserRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next,$roles)
    {
        if(!$request->user()->hasRole(explode('|',$roles))){
            throw new ApiException(403,'Forbidden for you');
        }
        return $next($request);
    }
}
