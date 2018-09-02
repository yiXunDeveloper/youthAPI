<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCulumnToServiceNewStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //修改新生信息表
        Schema::table('service_new_students', function (Blueprint $table) {
            $table->dropColumn(['id_card','nation']);    //删除身份证和民族
            $table->string('school',10)->nullable();   //增加 校区
            $table->string('dormitory',20)->nullable();  //宿舍楼号
            $table->string('room',10)->nullable();     //房间
            $table->integer('bed')->nullable();      //床位
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
