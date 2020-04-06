<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Status;

class ViewOnlyPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if(Auth::user()->level == 3){
            return response()->json(['message' => 'Not allowed'], Status::HTTP_METHOD_NOT_ALLOWED);
        }

        return $next($request);
    }
}
