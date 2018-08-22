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
    //事务中心参观start
    $api->post('preordain/login','PreordainController@login');
    $api->post('preordain/adminlogin','PreordainController@adminlogin');
    //学院可以进行的操作
    $api->group(['middleware'=>['auth:preordain','preordain.user']],function ($api){
       //学院预约
       $api->post('preordain/select','PreordainController@select');
       //学院删除预约
       $api->delete('preordain/select','PreordainController@deleteSelect');
    });
    $api->get('preordain/list','PreordainController@latestList');
    //获取学院信息
    $api->group(['middleware'=>['auth:preordain']],function ($api) {
        $api->get('preordain/userinfo','PreordainController@userinfo');
    });
    //管理员可以进行的操作
    $api->group(['middleware'=>['auth:preordain','preordain.admin']],function ($api){
        //获取上次设置的时间
        $api->get('preordain/time','PreordainController@lastTime');
        //设置时间段
        $api->post('preordain/time','PreordainController@setTime');
        //更新时间段（只能更新开始预约时间和结束预约时间）
        $api->put('preordain/time','PreordainController@updateTime');
    });
});