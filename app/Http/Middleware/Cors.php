<?php

namespace App\Http\Middleware;

use Closure;

class Cors
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
        $response = $next($request);
//        $origin = $request->server('HTTP_ORIGIN') ? $request->server('HTTP_ORIGIN') : '';
//        $allow_origin = [
//            'http://localhost:3000',
//            'https://lab.youthol.cn',
//            'http://oa.youthol.cn',
//        ];
//        if(in_array($origin,$allow_origin)){
//            $response->header('Access-Control-Allow-Origin', $origin);
//            $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Cookie, Accept, multipart/form-data, application/json,Authorization,responseType,X-Requested-With');
//            $response->header('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, OPTIONS, DELETE');
//            $response->headerX('Access-Control-Allow-Credentials', 'false');
//        }
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Cookie, Accept, multipart/form-data, application/json,Authorization,responseType,X-Requested-With');
        $response->header('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, OPTIONS, DELETE');
        $response->header('Access-Control-Allow-Credentials', 'false');
        return $response;
    }
}