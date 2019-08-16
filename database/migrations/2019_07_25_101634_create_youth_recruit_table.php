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
            $table->increments('id');
            $table->string('name',20);
            $table->string('sex',20);
            $table->string('nb',20)->unique();
            $table->string('phone',20)->unique();
            $table->string('email',20)->unique();
            $table->string('college',20);
            $table->string('class',20);
            $table->string('part_1',20);
            $table->string('part_2',20);
            $table->text('introduction',600);
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
