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
            $table->integer('seat')->nullable();
            $table->string('course',48)->nullable();
            $table->string('jxb',48)->nullable();
            $table->string('teacher',30)->nullable();
            $table->string('academy',30)->nullable();
            $table->string('class_name',30)->nullable();
            $table->string('sdut_id',11)->nullable();
            $table->string('name',30)->nullable();
            $table->string('exam_name','30')->nullable();
            $table->string('code',30)->nullable();
            $table->string('date',24)->nullable();
            $table->string('classroom',13)->nullable();
            $table->string('school',9)->nullable();
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
