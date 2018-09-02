<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOaWorkloadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //工作量统计表
        Schema::create('oa_workloads', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_id',11);   //用户学号
            $table->string('description')->nullable();  //工作量描述
            $table->integer('score')->nullable();  //工作积分
            $table->string('manager_id',11);   //操作者学号
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
        Schema::dropIfExists('oa_workloads');
    }
}
