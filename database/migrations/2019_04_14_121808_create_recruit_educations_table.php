<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitEducationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recruit_educations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('ben_code',40);
            $table->string('xue_code',40);
            $table->string('yan_code',40)->nullable();
            $table->string('shuo_code',40)->nullable();
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
        Schema::dropIfExists('recruit_educations');
    }
}
