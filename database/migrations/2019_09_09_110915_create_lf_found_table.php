<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLfFoundTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lf_found', function (Blueprint $table) {
            $table->increments('id');
            $table->string('found_name', 36);      // 名称
            $table->string('found_time', 10);      // 时间
            $table->string('found_place', 64);     // 地点
            $table->string('found_detail', 255);    // 描述/细节
            $table->string('found_img', 100);       // 图片id
            $table->string('found_person', 36);    // 找到物品的人
            $table->string('found_phone', 15);      // 俩你方式
//            $table->string('found_holder', 49);    // 物品存放处
            $table->integer('found_status')->defaoult(1);  // 物品状态 // 是否被领取
            $table->timestamps();                                  // 发布时间/修改时间
            $table->time('return_at')->nullable();// 归还时间

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lf_found');
    }
}
