<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQqUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qq_users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('qqapp_openid')->nullable();
                $table->string('qqapp_session_key')->nullable();

                $table->string('nickName')->nullable();
                $table->string('avatarUrl')->nullable();
                $table->date('last_actived_at')->nullable();
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
        Schema::dropIfExists('qq_users');
    }
}
