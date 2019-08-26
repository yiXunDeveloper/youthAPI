<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYouthRecruitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('youth_recruit', function (Blueprint $table) {
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('id');
            $table->string('name',30);
            $table->integer('sex');
            $table->string('nb', 11)->unique();
            $table->string('phone', 11)->unique();
            $table->string('email',30)->nullable();;
            $table->integer('college');
            $table->string('class',40);
            $table->integer('part_1');
            $table->integer('part_2');
            $table->text('introduction');
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
        Schema::dropIfExists('youth_recruit');
    }
}
