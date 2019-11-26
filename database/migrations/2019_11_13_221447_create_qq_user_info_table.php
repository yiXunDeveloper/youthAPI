<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQqUserInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qq_user_info', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 20);
            $table->string('school', 50);
            $table->integer('offical')->nullable();
            $table->integer('sex')->default(1);
            $table->text('des')->nullable();
            $table->string('tag')->nullable();
            $table->integer('level');
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
        Schema::dropIfExists('qq_user_info');
    }
}
