<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBhiTimeToPatientMonthlySummary extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('patient_monthly_summaries', 'bhi_time')) {
            Schema::table('patient_monthly_summaries', function (Blueprint $table) {
                $table->integer('bhi_time')
                      ->after('ccm_time')
                      ->default(0)
                      ->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_monthly_summaries', function (Blueprint $table) {
            $table->dropColumn('bhi_time');
        });
    }
}
