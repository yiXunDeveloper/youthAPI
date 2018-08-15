<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePreordainUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preordain_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username');//学院和后台登录的用户名
            $table->string('password');//密码
            $table->string('college')->nullable(); //学院名
            $table->string('admin')->default(0);//是否有管理员权限，0代表没有，1代表有
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
        Schema::dropIfExists('preordain_users');
    }
}
