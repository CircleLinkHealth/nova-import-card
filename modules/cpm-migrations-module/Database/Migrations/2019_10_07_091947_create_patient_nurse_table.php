<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatientNurseTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('patients_nurses');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('patients_nurses', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('patient_user_id');

            $table->unsignedInteger('nurse_user_id')
                ->nullable(true)
                ->default(null);

            $table->timestamps();

            $table->foreign('patient_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('CASCADE');

            $table->foreign('nurse_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('SET NULL');
        });
    }
}
