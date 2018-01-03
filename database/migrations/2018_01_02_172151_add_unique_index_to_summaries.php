<?php

use App\PatientMonthlySummary;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqueIndexToSummaries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (PatientMonthlySummary::get() as $summary) {
            PatientMonthlySummary::where('month_year', $summary->month_year)
                ->where('patient_id', $summary->patient_id)
                ->where('id', '!=', $summary->id)
                ->delete();
        }

        Schema::table('patient_monthly_summaries', function (Blueprint $table) {
            $table->unique(['patient_id', 'month_year']);
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
            //
        });
    }
}
