<?php

use App\Models\CCD\Problem;
use App\PatientMonthlySummary;
use Illuminate\Database\Migrations\Migration;

class AddCcdProblems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (PatientMonthlySummary::all() as $summ) {
            if ($summ->billable_problem1_code) {
                $ccdProblem1 = Problem::where('patient_id', $summ->patientInfo->user_id)
                                      ->where('icd_10_code', $summ->billable_problem1_code)
                                      ->first();

                if ($ccdProblem1) {
                    $summ->problem_1          = $ccdProblem1->id;
                    $ccdProblem1->icd_10_code = $summ->billable_problem1_code;
                    $ccdProblem1->billable    = true;
                    $ccdProblem1->save();
                }
            }

            if ($summ->billable_problem2_code) {
                $ccdProblem2 = Problem::where('patient_id', $summ->patientInfo->user_id)
                                      ->where('icd_10_code', $summ->billable_problem2_code)
                                      ->first();

                if ($ccdProblem2) {
                    $summ->problem_2          = $ccdProblem2->id;
                    $ccdProblem2->icd_10_code = $summ->billable_problem2_code;
                    $ccdProblem2->billable    = true;
                    $ccdProblem2->save();
                }
            }

            $summ->save();
        }
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
