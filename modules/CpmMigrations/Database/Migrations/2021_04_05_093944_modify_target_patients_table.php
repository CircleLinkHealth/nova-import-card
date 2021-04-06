<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class ModifyTargetPatientsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('target_patients', function (Blueprint $table) {
            $table->unsignedInteger('ehr_patient_id')->nullable(false)->change();
            $table->unsignedInteger('ehr_practice_id')->nullable(false)->change();
            $table->unsignedInteger('ehr_department_id')->nullable(false)->change();
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('target_patients', function (Blueprint $table) {
            $table->string('ehr_patient_id')->nullable(true)->change();
            $table->string('ehr_practice_id')->nullable(true)->change();
            $table->string('ehr_department_id')->nullable(true)->change();
        });
    }
}
