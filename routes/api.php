<?php

use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});
$api = app('Dingo\Api\Routing\Router');
$api->version('v1',[
    'namespace' => 'App\Http\Controllers\Api',
],function ($api){
    $api->get('question','FeatureController@question');

    $api->get('newstudent','FeatureController@newStudent');
    //事务中心参观
    $api->post('preordain/login','PreordainController@login');
    $api->post('preordain/adminlogin','PreordainController@adminlogin');
    $api->group(['middleware'=>['auth:preordain']],function ($api){
       $api->get('preordain/userinfo','PreordainController@userinfo');
    });
});