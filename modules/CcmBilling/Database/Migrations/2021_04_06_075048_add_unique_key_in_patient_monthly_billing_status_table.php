<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueKeyInPatientMonthlyBillingStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \CircleLinkHealth\CcmBilling\Entities\PatientMonthlyBillingStatus::truncate();
        Schema::table('patient_monthly_billing_statuses', function (Blueprint $table) {
            $table->unique(['patient_user_id', 'chargeable_month'], 'billing_statuses_patient_id_month_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_monthly_billing_statuses', function (Blueprint $table) {
            $table->dropForeign('pmbs_patient_user_id_foreign');
            $table->dropUnique('billing_statuses_patient_id_month_unique');
            $table->foreign('patient_user_id', 'pmbs_patient_user_id_foreign')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
}
