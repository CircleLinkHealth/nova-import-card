<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueKeyToChargeablePatientSummariesTable extends Migration
{
    const UNIQUE_KEY_NAME = 'patient_cs_summary_for_month';
    const TABLE_NAME = 'chargeable_patient_monthly_summaries';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary::truncate();

        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->unique(['patient_user_id', 'chargeable_month', 'chargeable_service_id'], self::UNIQUE_KEY_NAME);
        });

        \Illuminate\Support\Facades\Bus::chain([
            new \CircleLinkHealth\CcmBilling\Jobs\ProcessAllPracticePatientMonthlyServices(),
            new \CircleLinkHealth\CcmBilling\Jobs\CheckPatientSummariesHaveBeenCreated()
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table) {

            $table->dropForeign($patientKey = 'cpms_patient_user_id_foreign');
            $table->dropForeign($csKey = 'cpms_cs_id_foreign');
            $table->dropUnique(self::UNIQUE_KEY_NAME);
            $table->foreign('patient_user_id', $patientKey)
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('chargeable_service_id', $csKey)
                  ->references('id')
                  ->on('chargeable_services')
                  ->onDelete('cascade');

        });
    }
}
