<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuesInvestOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //创建问卷调查选项
        Schema::create('ques_invest_options', function (Blueprint $table) {
            $table->increments('id');
            $table->string('qkey',20)->nullable();   //问卷调查问题的key，用于关联问卷调查问题
           $table->string('field_label')->nullable();  //问卷调查选项的显示值
           $table->string('field_value')->nullable();   //传递值
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
        Schema::dropIfExists('ques_invest_options');
    }
}
