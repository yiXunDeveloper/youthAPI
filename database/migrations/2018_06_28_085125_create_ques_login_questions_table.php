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
        Schema::create('ques_login_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('catid')->nullable();
            $table->string('key',20)->unique();
            $table->integer('input_num')->nullable();
            $table->string('input_title')->nullable();
            $table->integer('input_type')->nullable();
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
