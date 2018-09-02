<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuesLoginQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //创建问卷登录问题表
        Schema::create('ques_login_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('catid')->nullable();  //所属问卷id
            $table->string('key',20)->unique();   //前端生成的key，具有唯一性，用于关联问卷登录选项
            $table->integer('input_num')->nullable();  //题号
            $table->string('input_title')->nullable();  //题目
            $table->integer('input_type')->nullable();  //题目类型，1是select选项，0是填空
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
        Schema::dropIfExists('ques_login_questions');
    }
}
