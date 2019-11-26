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
$api->version('v1', [
    'namespace' => 'App\Http\Controllers\Api',
    'middleware' => ['bindings'], //添加这个中间件才能使用模型绑定
], function ($api) {

    //转换
    $api->get('ques/transform', 'QuesController@transformAnswers');


    //问卷调查start

    $api->post('ques/register', 'QuesController@register'); //管理员注册
    $api->post('ques/login', 'QuesController@login');    //管理员登录
    //管理员进行的操作
    $api->group(['middleware' => ['api.auth']], function ($api) {
        $api->get('ques', 'QuesController@quesGet'); //问卷列表
        $api->post('ques/create', 'QuesController@quesCreate');  //创建问卷
        $api->delete('ques/{id}', 'QuesController@quesDelete');   //删除问卷及其关联
        $api->get('ques/{id}/export', 'QuesController@quesExport');   //导出调查问卷

    });
    $api->get('ques/{id}', 'QuesController@quesDetail'); //问卷详情
    //限制访问频率  1分钟1次
    $api->group([
        'middleware' => 'api.throttle',
        'limit'      => config('api.rate_limits.sign.limit'),
        'expires'    => config('api.rate_limits.sign.expires'),
    ], function ($api) {
        $api->post('ques/{id}', 'QuesController@quesStore');
    });
    //问卷调查end

    //事务中心参观start
    //所有人都可以进行的操作
    $api->post('preordain/login', 'PreordainController@login');  //学院登录
    $api->post('preordain/adminlogin', 'PreordainController@adminlogin'); //管理登录
    $api->get('preordain/time', 'PreordainController@lastTime'); //获取上次设置的时间
    $api->get('preordain/list', 'PreordainController@latestList'); //获取所有时间段
    //登陆后才可以进行的操作
    $api->group(['middleware' => ['api.auth']], function ($api) {
        $api->any('preordain/{id}/export', 'PreordainController@export'); //导出数据
        $api->get(
            'preordain/userinfo',
            'PreordainController@userinfo'
        ); //获取登录者的name
    });

    //学院可以进行的操作
    $api->group(
        ['middleware' => ['api.auth', 'preordain.user']],
        function ($api) {
            $api->post('preordain/select', 'PreordainController@select'); //学院预约
            $api->delete(
                'preordain/select/{id}',
                'PreordainController@deleteSelect'
            ); //学院删除预约
        }
    );

    //管理员可以进行的操作
    $api->group(
        ['middleware' => ['api.auth', 'preordain.admin']],
        function ($api) {
            $api->post('preordain/time', 'PreordainController@setTime'); //设置时间段
            $api->put(
                'preordain/time',
                'PreordainController@updateTime'
            ); //更新时间段（只能更新开始预约时间和结束预约时间）
        }
    );
    //事务中心参观end


    $api->get('oa/user/export', 'OAController@exportUser')
        ->middleware('api.auth');
    $api->post('oa/user/import', 'OAController@importUserInfo')
        ->middleware('api.auth');          //清空原有用户并导入


    //OA办公系统start
    $api->post('oa/login', 'OAController@login');    //登录
    $api->put('oa/refresh', 'OAController@refreshToken')
        ->middleware('api.auth');  //刷新token
    $api->get('oa/user', 'PermissionController@index')
        ->middleware('api.auth');    //获取当前登录用户信息

    //用户角色权限管理
    $api->get(
        'oa/permissions',
        'PermissionController@getPermissions'
    );    //获取所有权限
    $api->get(
        'oa/roles',
        'PermissionController@getRoles'
    );                //获取所有角色
    $api->get(
        'oa/permission/{permission}',
        'PermissionController@getPermissionById'
    ); //根据id获取权限信息
    $api->get(
        'oa/role/{role}',
        'PermissionController@getRoleById'
    );              //根据id获取角色及其权限

    $api->post('oa/user', 'PermissionController@addUser')
        ->middleware('api.auth');   //添加用户
    $api->get('oa/user/{youthUser}', 'PermissionController@getUserById')
        ->middleware('api.auth');   //根据id获取用户信息
    $api->put('oa/user/{youthUser}', 'PermissionController@updateUser')
        ->middleware('api.auth');   //修改用户信息，为用户分配角色
    $api->delete('oa/user/{youthUser}', 'PermissionController@deleteUser')
        ->middleware('api.auth');   //删除用户信息
    $api->post('oa/role', 'PermissionController@addRole')
        ->middleware('api.auth');   //添加角色
    $api->put('oa/role/{role}', 'PermissionController@updateRole')
        ->middleware('api.auth');   //修改角色信息，为角色分配权限
    $api->put(
        'oa/permission/{permission}',
        'PermissionController@updatePermission'
    )
        ->middleware('api.auth');   //修改权限名称
    $api->get('oa/users', 'OAController@getUsers');   //获取全部用户
    $api->get('oa/birthday', 'OAController@getBirthdayOfPeople');  //获取当天过生日的用户

    //签到签退
    $api->get('oa/signin', 'OAController@getSigninLists');  //获取当天签到列表
    $api->post('oa/signin', 'OAController@updateSignRecord');  //签到、签退
    $api->get('oa/signin/export', 'OAController@exportSignRecord')
        ->middleware('api.auth');      //签到记录导出


    //日程管理
    $api->get('oa/schedules', 'OAController@getSchedules');   //获取日程列表
    $api->get(
        'oa/schedule/{schedule}',
        'OAController@getScheduleById'
    );     //获取单个日程
    $api->post('oa/schedule', 'OAController@addSchedule');        //增加日程
    $api->put(
        'oa/schedule/{schedule}',
        'OAController@updateSchedule'
    );    //更新日程
    $api->delete('oa/schedule/{schedule}', 'OAController@deleteSchedule')
        ->middleware('api.auth');         //删除日程

    //设备管理
    $api->get(
        'oa/equipments',
        'OAController@getEquipments'
    );             //获取全部设备
    $api->get(
        'oa/equipment/{equipment}',
        'OAController@getEquipmentById'
    );               //获取单个设备信息
    $api->post(
        'oa/equipment',
        'OAController@addEquipment'
    );               //增加一个设备
    $api->put(
        'oa/equipment/{equipment}',
        'OAController@updateEquipment'
    );           //更新设备
    $api->delete('oa/equipment/{equipment}', 'OAController@deleteEquipment')
        ->middleware('api.auth');       //删除设备


    //设备借用记录管理
    $api->get(
        'oa/devices',
        'OAController@getEquipmentRecords'
    );     //获取所有设备借用记录
    $api->post('oa/device', 'OAController@addEquipmentRecord');  //增加借阅记录
    $api->put('oa/device/{record}', 'OAController@updateEquipmentRecord');  //更新
    $api->delete('oa/device/{record}', 'OAController@deleteEquipmentRecord')
        ->middleware('api.auth');  //删除记录

    //电话簿管理
    $api->get('oa/phonebooks', 'OAController@getPhonebooks');  //获取全部电话簿
    $api->post('oa/phonebook', 'OAController@addPhonebook')
        ->middleware('api.auth'); // 添加电话簿
    $api->post('oa/phonebook/import', 'OAController@importPhonebook')
        ->middleware('api.auth'); // 导入电话簿
    $api->get('oa/phonebook/export', 'OAController@exportPhonebook')
        ->middleware('api.auth'); //导出电话簿
    $api->put('oa/phonebook/{phonebook}', 'OAController@updatePhonebook')
        ->middleware('api.auth'); //更新电话簿
    $api->delete('oa/phonebook/{phonebook}', 'OAController@deletePhonebook')
        ->middleware('api.auth'); //删除电话簿

    //工作量管理
    $api->get('oa/workloads', 'OAController@getWorkloads');   //获取当月的任务量
    $api->post('oa/workload', 'OAController@addWorkload');      //添加工作量
    $api->get('oa/workload/export', 'OAController@exportWorkload'); //导出工作量
    $api->get(
        'oa/workload/{workload}',
        'OAController@getWorkloadById'
    ); //通过id获取任务量详情
    $api->put('oa/workload/{workload}', 'OAController@updateWorkload'); //更新任务量
    $api->delete(
        'oa/workload/{workload}',
        'OAController@deleteWorkload'
    );  //删除任务量

    //学生服务卫生成绩管理
    $api->get('oa/hygiene/weeks', 'OAController@getHW');  //获取卫生成绩周次
    $api->delete('oa/hygiene/weeks', 'OAController@deleteHW')
        ->middleware('api.auth'); //删除对应周次卫生成绩,接收weeks参数为数组
    $api->post('oa/hygiene/import', 'OAController@importHygiene')
        ->middleware('api.auth');        //宿舍卫生成绩导入

    //OA办公系统结束

    //全局数据
    $api->get('dormitory', 'FeatureController@dormitory');  //所有宿舍
    $api->get('college', 'FeatureController@college');  //所有学院


    //学生服务
    $api->get('service/authorization', 'FeatureController@authorization'); //登录
    $api->get('service/elec', 'FeatureController@elec');  //电费查询
    $api->get('service/hygiene', 'FeatureController@hygiene');  //宿舍卫生
    $api->get('service/exam', 'FeatureController@exam');    //考试时间

    //四六级查询
    $api->get('service/cet', 'FeatureController@cetGet');  //获取验证码 返回cookie
    $api->post('service/cet', 'FeatureController@cetPost'); //提交信息，获取考试成绩


    $api->get('service/user', 'FeatureController@index')
        ->middleware('api.auth');  //通过token获取个人信息
    $api->post('service/user', 'FeatureController@updateUser')
        ->middleware('api.auth'); //修改个人信息
    $api->delete('service/user', 'FeatureController@deleteUser')
        ->middleware('api.auth'); //删除个人信息


    $api->get('newstudent', 'FeatureController@newStudent');  // 新生信息查询
    $api->get(
        'service/freshmanNotice',
        'FeatureController@freshmanNotice'
    );  // 新生信息查询通告

    //留言板
    $api->get(
        'messageboard/getdata',
        'MessageBoardController@get_MessageBoard'
    );
    $api->post(
        'messageboard/insertdata',
        'MessageBoardController@insert_MessageBoard'
    );
    $api->get(
        'messageboard/delete/{id}/{key}',
        'MessageBoardController@delete_MessageBoard'
    );
    //留言板结束

    //失物招领 Lost and found 取首字母 laf
    //  $api->get('laf/getdata','LostAndFoundController@laf');//测试路由
    $api->get('laf/getdata/{method}/', 'LostAndFoundController@gainFinderOrTheOwnerReleaseInfor'); //获取数据
    $api->post('laf/insertdata/{method}', 'LostAndFoundController@finderOrTheOwnerRelease'); //添加数据
    $api->get('laf/deletedata/{id}/{method}', 'LostAndFoundController@deleteOneData'); //删除数据
    $api->post('laf/updatedata/{method}', 'LostAndFoundController@updateReleaseStatus'); //修改数据（状态码）

    //失物招领结束

    //小程序API

    // 纳新
    $api->get(
        'mini/departmentIntro',
        'MiniProgramController@getDepartmentIntro'
    );  // 部门介绍
    $api->post('mini/recruit', 'FeatureController@recruit');  // 信息提交
    $api->get('mini/recruit/notice', 'MiniProgramController@recruitNotice');
    //小程序结束

    //测试
    $api->post('test', 'Featurecontroller@test');
    $api->get('service/test', 'FeatureController@index');
});


//qq小程序开始
$api->version(
    'v1',
    [
        'namespace' => 'App\Http\Controllers\Qq',
    ],
    function ($api) {
        $api->post('qq/authorizations', 'LoginController@qqappStore');
        $api->put('qq/authorizations/current', 'LoginController@update')
            ->name('qq.authorizations.update');
        // 删除token
        $api->delete('qq/authorizations/current', 'LoginController@destroy')
            ->name('qq.authorizations.destroy');

        //首页热点基础功能
        //首页文章基本信息(文章信息+作者信息+点赞数+评论数)
        $api->get('qq/home/basic', 'GeneralPurposeController@getHomeArticleListBasicInfo')
            ->name('qq.home.basic');
        //获取根据Type分类后的文章列表 返回信息上同
        $api->get('qq/home/basic/{type}', 'GeneralPurposeController@getHomeArticleListBasicInfoByType')
            ->name('qq.home.basic.type');
        //单个文章评论详情信息获取(所有评论内容+发布评论内容的评论者)
        $api->get('qq/article/comment/{articleId}', 'GeneralPurposeController@getArticleCommentMainInfo')
            ->name('qq.article.comment');
        //获取热点文章点赞的相关信息
        $api->get('qq/article/good/{articleId}', 'GeneralPurposeController@getArticleGoodMainInfo')
            ->name('qq.article.good');

        $api->group(['middleware' => 'auth:qq'], function ($api) {
            // 当前登录用户信息
            $api->get('qq/user', 'LoginController@me')
                ->name('api.user.show');
            $api->post('qq/user/update', 'LoginController@meUpdate')
                ->name('api.user.show');
            $api->post('qq/article/create', 'Article@store')
                ->name('api.user.show');
            $api->post('qq/article/update', 'Article@update')
                ->name('api.user.show');
            $api->post('qq/picture', 'Article@pictStore')
                ->name('api.user.show');
            $api->post('qq/article/comment', 'Comment@store')
                ->name('api.user.comment.show');
            $api->post('qq/article/comment/updata', 'Comment@update')
                ->name('api.user.comment.updata');
            $api->post('qq/article/comment/delete/{id}', 'Comment@destroy')
                ->name('api.user.comment.delete');

            $api->get('qq/article/show/{id}', 'Article@show')
                ->name('api.user.show');
            $api->get('qq/article/list/show', 'Article@articleList')
                ->name('api.user.show');
            $api->get('qq/good/article/list', 'Article@zanArticle')
                ->name('api.user.show');
            $api->post('qq/article/type/list', 'Article@typeArticleList')
                ->name('api.user.show');
            $api->get('qq/article/delete/{id}', 'Article@delete')
                ->name('api.user.show');
            $api->get('qq/article/zan/{id}', 'ArticleGoodController@zan')
                ->name('api.user.show');
            $api->get('qq/personal/attention/{user_id}', 'FansController@fan')
                ->name('api.user.attention');
            $api->get('qq/fans', 'FansController@fansList')
                ->name('api.user.attention');
            $api->get('qq/faned', 'FansController@fanedList')
                ->name('api.user.attention');
            $api->get('qq/me/article/list/{id}', 'Article@meArticle')
                ->name('api.user.attention');
            $api->get('qq/article/collect/{article_id}', 'CollectController@collectOrNot')
                ->name('api.article.collection.show');
            $api->get('qq/article/collection/list', 'CollectController@collectionList')
                ->name('api.article.collection.show');

            /**
             * 资源路由 获取个人全部热点文章及其相关信息(get) <--注：暂时不用  处理数据过多  已经转由分步请求
             * +用户发布文章(post)+修改文章(put)+删除文章(delete)
             */
            Route::apiResource('article', 'Article');
            /**
             * 发布评论(post)+更新本人评论内容(put)+删除本人评论(delete)
             */
            Route::apiResource('comment', 'Comment');
            /**
             * 个人信息页的 发布动态数 个人关注用户总数 个人评论总数 个人赞过的文章总数 个人文章获赞总数
             * 当前仅支持get请求方法
             */
            Route::apiResource('userbasic', 'UserBasicShow');
            /**
             * 关注用户(post) 取消关注(delete)
             */
            Route::apiResource('fans', 'Fans');
            //个人信息页面详情信息获取  文章信息+评论总数+获赞总数
            $api->get('qq/person/publish/articles', 'UserBasicShowController@getPersonallyPublishedArticles')
                ->name('qq.person.publish.articles');
            //评论内容+评论者
            $api->get('qq/comment/about/info/{articleId}', 'UserBasicShowController@getCommentAboutInfo')
                ->name('qq.comment.about.info');
            //点赞者信息
            $api->get('qq/good/about/info/{articleId}', 'UserBasicShowController@getGoodAboutInfo')
                ->name('qq.good.about.info');
        });
    }
);
