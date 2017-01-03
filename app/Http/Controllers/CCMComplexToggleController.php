<?php

namespace App\Http\Controllers;

use App\Call;
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

        $patient = User::find($patientId);
        $date_index = Carbon::now()->firstOfMonth()->toDateString();

        $patientRecord = $patient
            ->patientInfo
            ->patientSummaries
            ->where('month_year', $date_index)->first();

        if (empty($patientRecord)) {

                $patientRecord = PatientMonthlySummary::updateCCMInfoForPatient(
                    $patient->patientInfo,
                    $patient->patientInfo->cur_month_activity_time
                );

                if (isset($input['complex'])) {

                    $patientRecord->is_ccm_complex = 1;

                } else {

                    $patient->is_ccm_complex = 0;

                }


        } else { // if exists

            if (isset($input['complex'])) {

                $patientRecord->is_ccm_complex = 1;

            } else {

                $patient->is_ccm_complex = 0;

            }

        }

        dd($patientRecord);

        $patientRecord->save();

        return redirect()->back();

    }

}
