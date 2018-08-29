<?php

namespace App\Http\Middleware\Api;

use Closure;
use Auth;
class QuesCheck
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
        $user = Auth::guard('ques')->user;

        return $next($request);
    }
}
