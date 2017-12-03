<?php

use App\Models\CCD\Problem;
use App\PatientMonthlySummary;
use Illuminate\Database\Migrations\Migration;

class AddBillableProblemsFromName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        PatientMonthlySummary::whereNull('problem_1')
                             ->orWhere('problem_1', '=', '')
                             ->chunk(1000, function ($summaries) {
                                 foreach ($summaries as $summ) {
                                     if ($summ->billable_problem1) {
                                         $ccdProblem1 = Problem::where('patient_id', $summ->patientInfo->user_id)
                                                               ->whereHas('cpmProblem', function ($q) use ($summ) {
                                                                   $q->where('name', $summ->billable_problem1);
                                                               })
                                                               ->first();

                                         if ($ccdProblem1) {
                                             $summ->problem_1 = $ccdProblem1->id;

                                             if ( ! $ccdProblem1->icd_10_code && $summ->billable_problem1_code) {
                                                 $ccdProblem1->icd_10_code = $summ->billable_problem1_code;
                                                 $ccdProblem1->billable = true;
                                                 $ccdProblem1->save();
                                             }
                                         }
                                     }

                                     $summ->save();
                                 }
                             });

        PatientMonthlySummary::whereNull('problem_2')
                             ->orWhere('problem_2', '=', '')
                             ->chunk(1000, function ($summaries) {
                                 foreach ($summaries as $summ) {
                                     if ($summ->billable_problem2) {
                                         $ccdProblem2 = Problem::where('patient_id', $summ->patientInfo->user_id)
                                                               ->whereHas('cpmProblem', function ($q) use ($summ) {
                                                                   $q->where('name', $summ->billable_problem2);
                                                               })
                                                               ->first();

                                         if ($ccdProblem2) {
                                             $summ->problem_2 = $ccdProblem2->id;

                                             if ( ! $ccdProblem2->icd_10_code && $summ->billable_problem2_code) {
                                                 $ccdProblem2->icd_10_code = $summ->billable_problem2_code;
                                                 $ccdProblem2->billable = true;
                                                 $ccdProblem2->save();
                                             }
                                         }
                                     }

                                     $summ->save();
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
        //
    }
}
