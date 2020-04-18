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
            $table->string('lost_name',100);
            $table->string('lost_time',10);
            $table->string('lost_place',100);
            $table->string('lost_detail',255);
            $table->string('lost_img',100)->nullable();
            $table->string('lost_person',100);
            $table->string('lost_phone',11);
            $table->integer('lost_status')->detault(0);
            $table->timestamps();
            $table->timestamp('found_at')->nullable();
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
