<?php

use App\PatientMonthlySummary;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalTime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if ( ! Schema::hasColumn('patient_monthly_summaries', 'total_time')) {
            Schema::table('patient_monthly_summaries', function (Blueprint $table) {
                $table->integer('total_time')
                      ->nullable()
                      ->default(0)
                      ->after('patient_id');
            });

            PatientMonthlySummary::orderBy('id')
                                 ->chunk(200, function ($summaries) {
                                     foreach ($summaries as $p) {
                                         $p->total_time = $p->ccm_time + $p->bhi_time;
                                         $p->save();
                                     }
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
            $table->dropColumn('total_time');
        });
    }
}
