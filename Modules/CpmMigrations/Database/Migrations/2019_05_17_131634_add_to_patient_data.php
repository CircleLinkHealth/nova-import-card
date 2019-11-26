<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddToPatientData extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table(
            'patient_data',
            function (Blueprint $table) {
                $table->dropColumn('provider');
                $table->dropColumn('primary_insurance');
                $table->dropColumn('secondary_insurance');
            }
        );
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table(
            'patient_data',
            function (Blueprint $table) {
                $table->string('provider')->after('mrn')->nullable();
                $table->string('primary_insurance')->after('provider')->nullable();
                $table->string('secondary_insurance')->after('primary_insurance')->nullable();
            }
        );
    }
}
