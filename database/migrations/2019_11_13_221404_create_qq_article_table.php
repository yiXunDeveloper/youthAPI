<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQqArticleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qq_article', function (Blueprint $table) {
            $table->increments('id');
            $table->string('content')->notNull();
            $table->integer('user_id')->notNull();
            $table->string('type')->default('1');//测试的时候为1
            $table->string('tag')->nullable();
            $table->text('pictures')->nullable();
            $table->integer('visible')->notNull()->default(1);//默认可见值为1
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
        Schema::dropIfExists('qq_article');
    }
}
