<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePreordainListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preordain_lists', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date');//参观日期
            $table->string('time',20);//参观当天的时间段
            $table->string('college')->nullable();//参观此时间段的学院名称
            $table->integer('order_id');//对应preordain_opens表的id
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
        Schema::dropIfExists('preordain_lists');
    }
}
