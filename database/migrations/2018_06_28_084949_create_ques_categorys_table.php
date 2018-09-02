<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuesCategorysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //创建调查问卷表
        Schema::create('ques_categorys', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();  //问卷标题
            $table->string('description')->nullable();  //问卷副标题/描述
            $table->boolean('user_required')->default(false);  //是否需要填写用户信息0不填写，1填写
            $table->integer('author')->nullable();  //创建问卷人的id
            $table->dateTime('start_at')->nullable();  //问卷开始时间
            $table->dateTime('end_at')->nullable();   //问卷结束时间
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
        Schema::dropIfExists('ques_categorys');
    }
}
