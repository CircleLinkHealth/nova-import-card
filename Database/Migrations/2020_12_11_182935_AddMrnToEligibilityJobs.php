<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMrnToEligibilityJobs extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('eligibility_jobs', function (Blueprint $table) {
            $table->dropColumn('patient_mrn');
        });

        Schema::table('eligibility_jobs', function (Blueprint $table) {
            $table->string('patient_mrn')->virtualAs('COALESCE(JSON_UNQUOTE(data->"$.mrn_number"),JSON_UNQUOTE(data->"$.mrn"))')->index();
        });
    }
}
