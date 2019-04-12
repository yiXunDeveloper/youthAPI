<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateZpUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zp_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_nb',18)->nullable()->unique();
            $table->string('name','30')->nullable();
            $table->string('phone',11);
            $table->string('email')->unique();
            $table->string('password',255)->nullable();
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
        Schema::dropIfExists('zp_users');
    }
}
