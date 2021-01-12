<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequiresPatientConsentColumnOnChargeablePatientMonthlySummariesTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chargeable_patient_monthly_summaries', function (Blueprint $table) {
            $table->dropColumn('requires_patient_consent');
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chargeable_patient_monthly_summaries', function (Blueprint $table) {
            $table->boolean('requires_patient_consent')->default(0)->after('is_fulfilled');
        });
    }
}
