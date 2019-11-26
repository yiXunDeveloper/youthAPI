<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOaSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //活动计划表
        Schema::create('oa_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->string('event_name',32);   //事件名称
            $table->string('event_place',90);    //活动地点
            $table->dateTIme('event_date');     //活动时间
            $table->integer('event_status')->default(0);  //活动状态 0未完成，1已完成
            $table->string('sponsor',11);     //发起人
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
        Schema::dropIfExists('oa_schedules');
    }
}
