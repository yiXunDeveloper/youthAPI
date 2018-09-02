<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuesLoginOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //创建问卷登录选项表
        Schema::create('ques_login_options', function (Blueprint $table) {
            $table->increments('id');
            $table->string('qkey',20)->nullable();   //所属问题的key，用于关联问卷登录问题
            $table->string('field_label')->nullable();  //选项显示的值
            $table->string('field_value')->nullable();   //选项传递的值
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
        Schema::dropIfExists('ques_login_options');
    }
}
