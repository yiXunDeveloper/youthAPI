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
//问卷调查start

    $api->post('ques/register','QuesController@register'); //管理员注册
    $api->post('ques/login','QuesController@login');    //管理员登录
    //管理员进行的操作
    $api->group(['middleware'=>['auth:ques']],function ($api){
        $api->get('ques','QuesController@quesGet');//问卷列表
        $api->post('ques/create','QuesController@quesCreate');  //创建问卷
        $api->delete('ques/{id}','QuesController@quesDelete');   //删除问卷及其关联
    });
    $api->get('ques/{id}','QuesController@quesDetail');//问卷详情
    //限制访问频率  1分钟60次
    $api->group([
        'middleware' => 'api.throttle',
        'limit' => config('api.rate_limits.sign.limit'),
        'expires' => config('api.rate_limits.sign.expires'),
    ],function ($api){
        $api->post('ques/{id}','QuesController@quesStore');
    });
//问卷调查end


//新生查询start
    $api->get('newstudent','FeatureController@newStudent');
//新生查询end


//事务中心参观start
    //所有人都可以进行的操作
    $api->post('preordain/login','PreordainController@login');  //学院登录
    $api->post('preordain/adminlogin','PreordainController@adminlogin');//管理登录
    $api->get('preordain/time','PreordainController@lastTime');//获取上次设置的时间
    $api->get('preordain/list','PreordainController@latestList');//获取所有时间段
    $api->any('preordain/export/{id}','PreordainController@export'); //导出数据

    //登陆后才可以进行的操作
    $api->group(['middleware'=>['auth:preordain']],function ($api) {
        $api->get('preordain/userinfo','PreordainController@userinfo');//获取登录者的name
    });

    //学院可以进行的操作
    $api->group(['middleware'=>['auth:preordain','preordain.user']],function ($api){
       $api->post('preordain/select','PreordainController@select');//学院预约
       $api->delete('preordain/select/{id}','PreordainController@deleteSelect'); //学院删除预约
    });

    //管理员可以进行的操作
    $api->group(['middleware'=>['auth:preordain','preordain.admin']],function ($api){
        $api->post('preordain/time','PreordainController@setTime');//设置时间段
        $api->put('preordain/time','PreordainController@updateTime');//更新时间段（只能更新开始预约时间和结束预约时间）
    });
//事务中心参观end




    //OA办公系统start
    $api->get('oa/signin','OAController@getSigninLists');
    $api->post('oa/signin','OAController@updateSignRecord');







});