<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceHygienesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_hygienes', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date');
            $table->integer('week');
            $table->string('dormitory');
            $table->string('room');
            $table->integer('score');
            $table->string('academy');
            $table->string('member');
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
        Schema::dropIfExists('service_hygienes');
    }
}
