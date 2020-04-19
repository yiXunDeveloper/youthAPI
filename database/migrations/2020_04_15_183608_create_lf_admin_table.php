<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLfAdminTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lf_admin', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 20);
            $table->string('password', 40);
            $table->integer('power'); // 9 最高权限 / 5 管理员权限  3 用户权限
            $table->string('login_key', '16')->nullable();
            $table->timestamp('last_request')->nullable();
            $table->timestamp('login_at')->nullable();
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
        Schema::dropIfExists('lf_admin');
    }
}
