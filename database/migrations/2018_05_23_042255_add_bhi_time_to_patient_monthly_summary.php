<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBhiTimeToPatientMonthlySummary extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_monthly_summaries', function(Blueprint $table)
		{
			$table->integer('bhi_time')->after('ccm_time')->default(0)->nullable();
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_monthly_summaries', function(Blueprint $table)
		{
			$table->dropColumn('bhi_time');
		});
    }
}
