<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuesInvestQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //创建问卷调查问题表
        Schema::create('ques_invest_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key',20)->unique();     //问卷调查问题的key，具有唯一性
            $table->integer('catid')->nullable();   //所属问卷的id
            $table->integer('input_num')->nullable(); //题号
            $table->string('input_title')->nullable(); //标题
            $table->integer('input_type')->nullable();  //类型，1单选，2多选，3填空
            $table->boolean('is_required')->default(false);  //是否必填
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
        Schema::dropIfExists('ques_invest_questions');
    }
}
