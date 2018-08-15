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
            $table->string('course',48)->nullable();
            $table->string('teacher',30)->nullable();
            $table->string('code',30)->nullable();
            $table->string('date',24)->nullable();
            $table->string('school',9)->nullable();
            $table->string('classroom',13)->nullable();
            $table->integer('student_num')->nullable();
            $table->string('academy',30)->nullable();
            $table->string('class_name',30)->nullable();
            $table->string('jiankao',9)->nullable();
            $table->integer('jiankao_num')->nullable();
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
