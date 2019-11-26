<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQqUserBasicInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qq_users_basic_info', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable();
            $table->string('count')->nullable();
            $table->string('nickName')->nullable();
            $table->integer('gender')->nullable();
            $table->string('avatarUrl')->nullable();
            $table->string('language')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('country')->nullable();
            $table->string('name', 20)->nullable();
            $table->string('school')->nullable();
            $table->integer('offical')->nullable();
            $table->text('des')->nullable();
            $table->string('tags')->nullable();
            $table->integer('level')->nullable();
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
        Schema::dropIfExists('qq_user_basic_info');
    }
}
