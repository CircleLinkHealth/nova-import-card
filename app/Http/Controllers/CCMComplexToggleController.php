<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Algorithms\Invoicing\AlternativeCareTimePayableCalculator;
use App\PatientMonthlySummary;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CCMComplexToggleController extends Controller
{
    public function toggle(
        Request $request,
        $patientId
    ) {
        $input = $request->all();

        $date = Carbon::now()->startOfMonth()->toDateString();

        $patient = User::where('id', $patientId)
            ->with([
                'patientSummaries' => function ($q) use ($date) {
                    $q->where('month_year', $date);
                },
            ])
            ->first();

        $patientRecord = $patient
            ->patientSummaries
            ->first();

        if (empty($patientRecord)) {
            //should not need to do that, because there is a command on start of every month
            //that sets a monthly summary to 0 for each patient
            $patientRecord = PatientMonthlySummary::updateCCMInfoForPatient(
                $patient->id,
                0
            );
        }

        if (isset($input['complex'])) {
            $patientRecord->is_ccm_complex = 1;
            $patientRecord->save();

            if ($patient->getCcmTime() > 3600) {
                //Get nurse that did the last activity.
                $nurse = $patient->patientInfo->lastNurseThatPerformedActivity();
                if ($nurse) {
                    (new AlternativeCareTimePayableCalculator($nurse))
                        ->adjustPayOnCCMComplexSwitch60Mins();
                }
            }
        } else {
            $patientRecord->is_ccm_complex = 0;
            $patientRecord->save();
        }

        return response()->json(['patientSummary' => $patientRecord]);
    }
}
