<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePreordainOpensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preordain_opens', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('open_at');//预约开放时间
            $table->dateTime('close_at');//预约关闭时间
            $table->dateTime('start_at');//参观开放时间
            $table->dateTime('end_at');//参观关闭时间
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
        Schema::dropIfExists('preordain_opens');
    }
}
