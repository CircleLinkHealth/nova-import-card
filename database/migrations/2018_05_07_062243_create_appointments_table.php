<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAppointmentsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('appointments');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_id')->unsigned()->index('appointments_patient_id_foreign');
            $table->integer('author_id')->unsigned()->index('appointments_author_id_foreign');
            $table->integer('provider_id')->unsigned()->nullable()->index('appointments_provider_id_foreign');
            $table->date('date');
            $table->time('time');
            $table->text('status', 65535);
            $table->text('comment', 65535);
            $table->text('type', 65535);
            $table->boolean('was_completed');
            $table->timestamps();
        });
    }
}
