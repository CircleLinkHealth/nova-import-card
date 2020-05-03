<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddVirtualColumnsToEligibilityJob extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('eligibility_jobs', function (Blueprint $table) {
            $table->dropColumn('patient_first_name');
            $table->dropColumn('patient_last_name');
            $table->dropColumn('patient_mrn');
            $table->dropColumn('patient_dob');
            $table->dropColumn('patient_email');
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('eligibility_jobs', function (Blueprint $table) {
            $table->string('patient_first_name')->virtualAs('JSON_UNQUOTE(data->"$.first_name")')->index();
            $table->string('patient_last_name')->virtualAs('JSON_UNQUOTE(data->"$.last_name")')->index();
            $table->string('patient_mrn')->virtualAs('JSON_UNQUOTE(data->"$.mrn_number")')->index();
            $table->string('patient_dob')->virtualAs('DATE(JSON_UNQUOTE(data->"$.dob"))')->index();
            $table->string('patient_email')->nullable()->virtualAs('IF(JSON_UNQUOTE(data->"$.email") = "null", NULL, JSON_UNQUOTE(data->"$.email"))')->index();
        });
    }
}
