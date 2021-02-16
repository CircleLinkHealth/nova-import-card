<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class SetCsIdAndUserIdCallProblems extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('call_problems')
            ->join('patient_monthly_summaries', 'call_problems.patient_monthly_summary_id', '=', 'patient_monthly_summaries.id')
            ->where(function ($q) {
                $q->whereNull('call_problems.patient_user_id')
                    ->orWhereNull('call_problems.chargeable_month');
            })
            ->orderBy('call_problems.created_at', 'asc')
            ->selectRaw('call_problems.id as cp_id, patient_monthly_summaries.patient_id, patient_monthly_summaries.month_year')
            ->chunk(100, function ($records) {
                $records->each(function ($record) {
                    DB::table('call_problems')
                        ->where('id', '=', $record->cp_id)
                        ->update([
                            'patient_user_id'  => $record->patient_id,
                            'chargeable_month' => $record->month_year,
                        ]);
                });
            });
    }
}
