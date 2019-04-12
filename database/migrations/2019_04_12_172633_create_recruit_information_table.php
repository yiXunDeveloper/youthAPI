<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitInformationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recruit_information', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('name');
            $table->string('political_status');
            $table->string('birthplace',70);
            $table->string('now_work_place',50);
            $table->string('highest_education',20);
            $table->string('professional_code',20);
            $table->date('graduated_time');
            $table->string('sex');
            $table->string('nation');
            $table->string('marriage');
            $table->string('file_unit',50);
            $table->string('highest_degree');
            $table->string('graduated_school',20);
            $table->string('birth_year');
            $table->string('birth_mouth');
            $table->string('join_party_time')->nullable();
            $table->string('learn_subject',30);
            $table->string('apply_position');
            $table->integer('is_graduates');
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
        Schema::dropIfExists('recruit_information');
    }
}
