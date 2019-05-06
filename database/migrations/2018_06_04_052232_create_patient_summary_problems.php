<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatientSummaryProblems extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('patient_summary_problems');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('patient_summary_problems', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('patient_summary_id');
            $table->unsignedInteger('problem_id');
            $table->string('name');
            $table->string('icd_10_code')->nullable();
            $table->enum('type', ['ccm', 'bhi']);
            $table->timestamps();

            $table->foreign('patient_summary_id')
                ->references('id')
                ->on('patient_monthly_summaries')
                ->onUpdate('cascade');

            $table->foreign('problem_id')
                ->references('id')
                ->on('ccd_problems')
                ->onUpdate('cascade');

            $table->unique(['patient_summary_id', 'problem_id', 'type'], 'summary_id_patient_id_type_unique');
        });
    }
}
