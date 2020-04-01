<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQqCommentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qq_comment', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->notNull();
            $table->integer('article_id')->notNull();
            $table->integer('comment_id')->nullable();
            $table->string('content')->notNull();
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
        Schema::dropIfExists('qq_comment');
    }
}
