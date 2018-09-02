<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOaYouthUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //用户信息表
        Schema::create('oa_youth_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',64);    //姓名
            $table->string('sdut_id',11)->unique();   //学号
            $table->string('department',24);  //部门名称
            $table->integer('grade')->nullable();   //年级
            $table->string('phone',11)->nullable();    //手机号
            $table->date('birthday')->nullable();  //生日
//            $table->integer('status')->default(0);     //0试用 1正式  2退站
            //这是角色 分为 试用 正式 站长 副站 主任 管理 退站
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oa_youth_users');
    }
}
