<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLfLostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lf_lost', function (Blueprint $table) {
            $table->increments('id');
            $table->string('lost_man',100);
            $table->string('lost_verify',100);
            $table->string('lost_phone',11);
            $table->string('lost_thing',100);
            $table->string('lost_time',100);
            $table->string('lost_place',100);
            $table->string('lost_detail',100);
            $table->string('lost_img',100);
            $table->integer('lost_status')->detault(0);
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
        Schema::dropIfExists('lf_lost');
    }
}
