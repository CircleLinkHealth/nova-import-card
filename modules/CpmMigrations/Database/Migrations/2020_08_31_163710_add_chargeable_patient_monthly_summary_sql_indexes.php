<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChargeablePatientMonthlySummarySqlIndexes extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lv_activities', function (Blueprint $table) {
            $table->dropIndex('lv_activities_patient_summaries_view_index');
        });

        Schema::table('calls', function (Blueprint $table) {
            $table->dropIndex('calls_patient_summaries_view_index');
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lv_activities', function (Blueprint $table) {
            $table->index([
                'patient_id',
                'chargeable_service_id',
                'performed_at',
            ], 'lv_activities_patient_summaries_view_index');
        });

        Schema::table('calls', function (Blueprint $table) {
            $table->index([
                'inbound_cpm_id',
                'status',
                'called_date',
                'type',
                'sub_type',
            ], 'calls_patient_summaries_view_index');
        });
    }
}
