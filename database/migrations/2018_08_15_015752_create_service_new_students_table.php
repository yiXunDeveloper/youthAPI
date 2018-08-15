<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceNewStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_new_students', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name','30')->nullable();
            $table->string('sdut_id',11)->nullable();
            $table->string('college',50)->nullable();
            $table->string('major',50)->nullable();
            $table->string('class',20)->nulable();
            $table->integer('xuezhi')->nullable();
            $table->string('kaohao')->nullable();
            $table->string('nation',30)->nullable();
            $table->string('id_card',18)->nullable();
            $table->string('education',20)->nullable();
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
        Schema::dropIfExists('service_new_students');
    }
}
