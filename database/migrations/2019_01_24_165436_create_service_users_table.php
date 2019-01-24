<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('sdut_id',11)->nullable()->unique();
            $table->string('openid');
            $table->integer('college_id')->nullable();
            $table->string('class')->nullable();
            $table->integer('dormitory_id')->nullable();
            $table->integer('room')->nullable();
            $table->string('password_jwc')->nullable();
            $table->string('password_dt')->nullable();
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
        Schema::dropIfExists('service_users');
    }
}
