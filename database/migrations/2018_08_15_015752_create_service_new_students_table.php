<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceNewStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //创建新生信息表
        Schema::create('service_new_students', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name','30')->nullable();   //姓名
            $table->string('sdut_id',11)->nullable();  //学号
            $table->string('sex',3)->nullable();    //性别
            $table->string('college',50)->nullable();  //学院
            $table->string('major',50)->nullable();   //专业
            $table->string('class',20)->nulable();   //班级
            $table->integer('xuezhi')->nullable();   //学制
            $table->string('kaohao')->nullable();   //考号
            $table->string('nation',30)->nullable();   //民族
            $table->string('id_card',18)->nullable();   //身份证
            $table->string('education',20)->nullable();   //学历
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
        Schema::dropIfExists('service_new_students');
    }
}
