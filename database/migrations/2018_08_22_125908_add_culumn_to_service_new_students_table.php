<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCulumnToServiceNewStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_new_students', function (Blueprint $table) {
            $table->dropColumn(['id_card','nation']);
            $table->string('school',10)->nullable();
            $table->string('dormitory',20)->nullable();
            $table->string('room',10)->nullable();
            $table->integer('bed')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
