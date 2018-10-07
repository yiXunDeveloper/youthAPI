<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceExamMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_exam_metas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('course',48)->nullable();  //课程名称
            $table->string('teacher',30)->nullable();  //教师
            $table->string('class_composition',80)->nullable();   //班级组成
            $table->string('code',30)->nullable();    //试卷编号
            $table->string('date',24)->nullable();    //考试时间
            $table->string('school',9)->nullable();   //校区
            $table->string('classroom',13)->nullable();  //考场
            $table->integer('student_num')->nullable();  //考试人数
//            $table->string('academy',30)->nullable();
            $table->string('jiankao',9)->nullable();    //监考学院
            $table->integer('jiankao_num')->nullable();  //监考人数
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
        Schema::dropIfExists('service_exam_metas');
    }
}
