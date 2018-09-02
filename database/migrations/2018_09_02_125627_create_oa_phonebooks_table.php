<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOaPhonebooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //办公电话簿
        Schema::create('oa_phonebooks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('administrative_unit',32);  //行政单位
            $table->string('office_location',32);     //办公室地址
            $table->string('office_persion');        //办公室人员
            $table->string('telephone',11)->nullable(); //电话
            $table->string('notation',32)->nullable();  //备注
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
        Schema::dropIfExists('oa_phonebooks');
    }
}
