<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatientAWVSummariesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('patient_awv_summaries');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('patient_awv_summaries', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('year');
            $table->unsignedInteger('patient_id');
            $table->date('month_year');
            $table->dateTime('initial_visit')->nullable();
            $table->dateTime('subsequent_visit')->nullable();
            $table->boolean('is_billable')->default(0);
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('patient_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
}
