<?php

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
            $patientRecord = PatientMonthlySummary::updateCCMInfoForPatient(
                $patient->id,
                $patient->patientInfo->cur_month_activity_time
            );

            if (isset($input['complex'])) {
                $patientRecord->is_ccm_complex = 1;
                $patientRecord->save();

                if ($patient->patientInfo->cur_month_activity_time > 3600) {
                    //Get nurse that did the last activity.

                    (new AlternativeCareTimePayableCalculator($patient->patientInfo->lastNurseThatPerformedActivity()))
                        ->adjustPayOnCCMComplexSwitch60Mins();
                }
            } else {
                $patientRecord->is_ccm_complex = 0;
                $patientRecord->save();
            }
        } else { // if exists

            if (isset($input['complex'])) {
                $patientRecord->is_ccm_complex = 1;
                $patientRecord->save();

                if ($patient->patientInfo->cur_month_activity_time > 3600) {
                    (new AlternativeCareTimePayableCalculator($patient->patientInfo->lastNurseThatPerformedActivity()))->adjustPayOnCCMComplexSwitch60Mins();
                }
            } else {
                $patientRecord->is_ccm_complex = 0;
                $patientRecord->save();
            }
        }

        return response()->json(['patientSummary' => $patientRecord]);
    }
}
