<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuesAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //创建调查问卷管理用户表
        Schema::create('ques_admins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username')->nullable();  //用户名
            $table->string('password')->nullable();  //密码
            $table->string('name')->nullable();   //姓名
            $table->integer('admin')->default(0);   //是否为超级管理员，超级管理员可以操作所有问卷
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
        Schema::dropIfExists('ques_admins');
    }
}
