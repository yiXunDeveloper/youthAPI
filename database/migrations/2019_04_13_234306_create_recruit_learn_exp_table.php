<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitLearnExpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recruit_learn_exp', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->date('join_time');
            $table->date('graduate_time');
            $table->string('graduate_school',40);
            $table->string('major',40);
            $table->string('education');
            $table->string('bachelor');
            $table->string('learn_way');
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
        Schema::dropIfExists('recruit_learn_exp');
    }
}
