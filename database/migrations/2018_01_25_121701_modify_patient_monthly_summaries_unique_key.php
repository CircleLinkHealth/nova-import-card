<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyPatientMonthlySummariesUniqueKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_monthly_summaries', function (Blueprint $table) {

            $keyExists = DB::select(
                DB::raw(
                    'SHOW KEYS
                    FROM patient_monthly_summaries
                    WHERE Key_name=\'patient_monthly_summaries_patient_id_month_year_unique\''
                )
            );

            if (!$keyExists) {
                $table->unique(['patient_id', 'month_year']);
            }

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
            $table->dropUnique(['patient_id', 'month_year']);
        });
    }
}
