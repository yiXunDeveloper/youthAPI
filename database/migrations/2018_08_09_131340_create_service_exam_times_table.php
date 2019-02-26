<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceExamTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_exam_times', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seat')->nullable();  //座号
            $table->string('course',48)->nullable();  //课程名称
//            $table->string('jxb',48)->nullable();
            $table->string('teacher',30)->nullable();   //教师信息
            $table->string('date',24)->nullable();     //考试时间
            $table->string('classroom',13)->nullable(); //考试地点
            $table->string('school',9)->nullable();     //校区名称
            $table->string('college',30)->nullable();    //学院
            $table->string('class_name',30)->nullable(); //班级
            $table->string('sdut_id',11)->nullable();    //学号
            $table->string('name',30)->nullable();      //姓名
//            $table->string('exam_name','30')->nullable();
            $table->string('code',30)->nullable();    //试卷编号
            $table->tinyInteger('note')->default(0); //备注  暂时用来 0代表正常考试，1代表补考
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
        Schema::dropIfExists('service_exam_times');
    }
}
