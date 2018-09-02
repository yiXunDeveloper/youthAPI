<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOaEquipmentRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oa_equipment_records', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('device_id'); //??
            $table->string('activity');
            $table->dateTime('lend_at');
            $table->string('lend_user');   //这是谁
            $table->string('memome_user');   //oa_youth_user_id?
            $table->dateTime('return_at');
            $table->string('remome_user',64);   //id?
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
        Schema::dropIfExists('oa_equipment_records');
    }
}
