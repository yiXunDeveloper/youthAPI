<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceRongyusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_rongyus', function (Blueprint $table) {
            $table->increments('id');
            $table->string('sdut_name',30)->nullable();
            $table->string('sdut_id',11);
            $table->string('academy',30)->nullable();
            $table->string('class_name',30)->nullable();
            $table->string('rongyu',20)->nullable();
            $table->string('eval_year',30)->nullable();
            $table->string('eval_term',30)->nullable();
            $table->string('grade',4)->nullable();
            $table->string('sex',3)->nullable();
            $table->string('nation',6)->nullable();
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
        Schema::dropIfExists('service_rongyus');
    }
}
