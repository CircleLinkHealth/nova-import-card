<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_sessions', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('patient_id');
            $table->foreign('patient_id')
                ->references('ID')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')
                ->references('ID')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

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
        Schema::drop('patient_sessions');
    }
}
