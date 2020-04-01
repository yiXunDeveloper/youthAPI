<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLfFoundTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lf_found', function (Blueprint $table) {
            $table->increments('id');
            $table->string('found_man',100);
            $table->string('found_verify',100);
            $table->string('found_phone',11);
            $table->string('found_thing',100);
            $table->string('found_time',100);
            $table->string('found_place',100);
            $table->string('found_holder',100);
            $table->string('found_detail',255);
            $table->string('found_img',100);
            $table->integer('found_status')->defaoult(0);
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
        Schema::dropIfExists('lf_found');
    }
}
