<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitYanPositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recruit_yan_positions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('begin_time');
            $table->string('over_time');
            $table->string('witness_name');
            $table->string('witness_position',10);
            $table->string('witness_phone',12);
            $table->string('position',20);
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
        Schema::dropIfExists('recruit_yan_positions');
    }
}
