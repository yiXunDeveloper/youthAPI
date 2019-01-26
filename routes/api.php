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

//Route::middleware('api.auth')->get('/user', function (Request $request) {
//    return $request->user();
//});
$api = app('Dingo\Api\Routing\Router');
$api->version('v1',[
    'namespace' => 'App\Http\Controllers\Api',
    'middleware'=>['bindings'], //添加这个中间件才能使用模型绑定
],function ($api){

    //转换
    $api->get('ques/transform','QuesController@transformAnswers');


//问卷调查start

    $api->post('ques/register','QuesController@register'); //管理员注册
    $api->post('ques/login','QuesController@login');    //管理员登录
    //管理员进行的操作
    $api->group(['middleware'=>['api.auth']],function ($api){
        $api->get('ques','QuesController@quesGet');//问卷列表
        $api->post('ques/create','QuesController@quesCreate');  //创建问卷
        $api->delete('ques/{id}','QuesController@quesDelete');   //删除问卷及其关联
        $api->get('ques/{id}/export','QuesController@quesExport');   //导出调查问卷

    });
    $api->get('ques/{id}','QuesController@quesDetail');//问卷详情
    //限制访问频率  1分钟1次
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
    //登陆后才可以进行的操作
    $api->group(['middleware'=>['api.auth']],function ($api) {
        $api->any('preordain/{id}/export','PreordainController@export'); //导出数据
        $api->get('preordain/userinfo','PreordainController@userinfo');//获取登录者的name
    });

    //学院可以进行的操作
    $api->group(['middleware'=>['api.auth','preordain.user']],function ($api){
       $api->post('preordain/select','PreordainController@select');//学院预约
       $api->delete('preordain/select/{id}','PreordainController@deleteSelect'); //学院删除预约
    });

    //管理员可以进行的操作
    $api->group(['middleware'=>['api.auth','preordain.admin']],function ($api){
        $api->post('preordain/time','PreordainController@setTime');//设置时间段
        $api->put('preordain/time','PreordainController@updateTime');//更新时间段（只能更新开始预约时间和结束预约时间）
    });
//事务中心参观end


    $api->get('oa/user/export','OAController@exportUser')->middleware('api.auth');
    $api->post('oa/user/import','OAController@importUserInfo')->middleware('api.auth');          //清空原有用户并导入


//OA办公系统start
    $api->post('oa/login','OAController@login');    //登录
    $api->get('oa/user','PermissionController@index')->middleware('api.auth');    //获取当前登录用户信息


    $api->get('oa/permissions','PermissionController@getPermissions');    //获取所有权限
    $api->get('oa/roles','PermissionController@getRoles');                //获取所有角色
    $api->get('oa/permission/{id}','PermissionController@getPermissionById');//根据id获取权限信息
    $api->get('oa/role/{id}','PermissionController@getRoleById');              //根据id获取角色及其权限

    $api->get('oa/user/{id}','PermissionController@getUserById')->middleware('api.auth');   //根据id获取用户信息
    $api->put('oa/user/{id}','PermissionController@updateUser')->middleware('api.auth');   //修改用户信息，为用户分配角色
    $api->put('oa/role/{id}','PermissionController@updateRole')->middleware('api.auth');   //修改角色信息，为角色分配权限
    $api->put('oa/permission/{id}','PermissionController@updatePermission')->middleware('api.auth');   //修改权限名称

    $api->get('oa/users','OAController@getUsers');   //获取全部用户
    $api->get('oa/signin','OAController@getSigninLists');  //获取当天签到列表
    $api->post('oa/signin','OAController@updateSignRecord');  //签到、签退

    $api->get('oa/schedules','OAController@getSchedules');   //获取日程列表
    $api->get('oa/schedule/{schedule}','OAController@getScheduleById');     //获取单个日程
    $api->post('oa/schedule','OAController@addSchedule');        //增加日程
    $api->put('oa/schedule/{schedule}','OAController@updateSchedule');    //更新日程
    $api->delete('oa/schedule/{schedule}','OAController@deleteSchedule')->middleware('api.auth');         //删除日程!!加权限验证

    $api->get('oa/equipments','OAController@getEquipments');             //获取全部设备
    $api->get('oa/equipment/{equipment}','OAController@getEquipmentById');               //获取单个设备信息
    $api->post('oa/equipment','OAController@addEquipment');               //增加一个设备
    $api->put('oa/equipment/{equipment}','OAController@updateEquipment');           //更新设备
    $api->delete('oa/equipment/{equipment}','OAController@deleteEquipment')->middleware('api.auth');       //删除设备   验证

    $api->get('oa/devices','OAController@getEquipmentRecords');     //获取所有设备借用记录
    $api->post('oa/device','OAController@addEquipmentRecord');  //增加借阅记录
    $api->put('oa/device/{record}','OAController@updateEquipmentRecord');  //更新
    $api->delete('oa/device/{record}','OAController@deleteEquipmentRecord')->middleware('api.auth');  //删除

    $api->get('oa/phonebooks','OAController@getPhonebooks');  //获取全部电话簿
    $api->post('oa/phonebook','OAController@addPhonebook')->middleware('api.auth'); // 添加电话簿
    $api->post('oa/phonebook/import','OAController@importPhonebook')->middleware('api.auth'); // 导入电话簿
    $api->get('oa/phonebook/export','OAController@exportPhonebook')->middleware('api.auth'); //导出电话簿
    $api->put('oa/phonebook/{phonebook}','OAController@updatePhonebook')->middleware('api.auth'); //更新电话簿
    $api->delete('oa/phonebook/{phonebook}','OAController@deletePhonebook')->middleware('api.auth');//删除电话簿

    $api->get('oa/workloads','OAController@getWorkloads');   //获取当月的任务量
    $api->post('oa/workload','OAController@addWorkload');      //添加工作量
    $api->get('oa/workload/export','OAController@exportWorkload');//导出工作量
    $api->get('oa/workload/{workload}','OAController@getWorkloadById'); //通过id获取任务量详情
    $api->put('oa/workload/{workload}','OAController@updateWorkload'); //更新任务量
    $api->delete('oa/workload/{workload}','OAController@deleteWorkload');  //删除任务量

    $api->post('oa/hygiene/import','OAController@importHygiene')->middleware('api.auth');        //宿舍卫生成绩导入
    $api->get('oa/signin/export','OAController@exportSignRecord')->middleware('api.auth');      //签到记录导出

//OA办公系统end

//测试
    $api->get('service/test','FeatureController@index');

//全局数据
    $api->get('dormitory','FeatureController@dormitory');  //所有宿舍
//学生服务功能API
    $api->get('service/authorization','FeatureController@authorization');
    $api->get('service/elec','FeatureController@elec');  //电费查询
    $api->get('service/hygiene','FeatureController@hygiene');  //宿舍卫生
    $api->get('service/exam','FeatureController@exam');    //考试时间

    $api->get('service/user','FeatureController@index')->middleware('api.auth');  //通过token获取个人信息
    $api->post('service/user','FeatureController@updateUser')->middleware('api.auth'); //修改个人信息
//权限管理
    $api->get('test','Featurecontroller@test');


});