<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClosedStatusColumnToPatientMonthlySummariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_monthly_summaries', function (Blueprint $table) {
            $table->string('closed_ccm_status')
                  ->after('patient_id')
                  ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_monthly_summaries', function (Blueprint $table) {
            $table->dropColumn('closed_ccm_status');
        });
    }
}
