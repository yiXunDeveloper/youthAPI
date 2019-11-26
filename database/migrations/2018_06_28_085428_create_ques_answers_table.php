<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuesAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //创建用户答案表
        Schema::create('ques_answers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('catid')->nullable();  //所属问卷id
            $table->longText('userinfo')->nullable(); //用户信息 json格式
            $table->longText('answers')->nullable();  //用户答案，json格式
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
        Schema::dropIfExists('ques_answers');
    }
}
