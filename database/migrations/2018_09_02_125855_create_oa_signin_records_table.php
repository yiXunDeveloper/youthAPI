<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOaSigninRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //签到记录表
        Schema::create('oa_signin_records', function (Blueprint $table) {
            $table->increments('id');
            $table->string('sdut_id',11);            //学号
            $table->integer('status')->default(0);    // 0未签退，1正常，2多余值班，3早退，4无效
            $table->integer('duration')->default(0);   //持续时长
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
        Schema::dropIfExists('oa_signin_records');
    }
}
