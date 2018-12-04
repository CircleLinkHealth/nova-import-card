<?php

namespace App\Http\Controllers\Patient\Traits;

use Illuminate\Http\Request;

trait BiometricUserTraits
{
    public function getBiometrics($userId)
    {
        return response()->json($this->biometricUserService->patientBiometrics($userId));
    }

    public function removeBiometric($userId, $id)
    {
        return response()->json($this->biometricUserService->removePatientBiometric($userId, $id));
    }

    public function addBiometric($userId, Request $request)
    {
        $biometricId = $request->input('biometric_id');
        $starting = $request->input('starting');
        $target = $request->input('target');
        $systolic_high_alert = $request->input('systolic_high_alert');
        $systolic_low_alert = $request->input('systolic_low_alert');
        $diastolic_high_alert = $request->input('diastolic_high_alert');
        $diastolic_low_alert = $request->input('diastolic_low_alert');
        $high_alert = $request->input('high_alert');
        $low_alert = $request->input('low_alert');
        $starting_a1c = $request->input('starting_a1c');
        $monitor_changes_for_chf = $request->input('monitor_changes_for_chf');
        $result = null;
        if ($biometricId) {
            switch ($biometricId) {
                case 1:
                    $result = $this->biometricUserService->addPatientWeight($userId, $biometricId, [
                        'starting' => $starting,
                        'target' => $target,
                        'monitor_changes_for_chf' => $monitor_changes_for_chf
                    ]);
                    break;
                case 2:
                    $result = $this->biometricUserService->addPatientBloodPressure($userId, $biometricId, [
                        'starting' => $starting,
                        'target' => $target,
                        'diastolic_high_alert' => $diastolic_high_alert,
                        'diastolic_low_alert' => $diastolic_low_alert,
                        'systolic_high_alert' => $systolic_high_alert,
                        'systolic_low_alert' => $systolic_low_alert
                    ]);
                    break;
                case 3:
                    $result = $this->biometricUserService->addPatientBloodSugar($userId, $biometricId, [
                        'starting' => $starting,
                        'target' => $target,
                        'high_alert' => $high_alert,
                        'low_alert' => $low_alert,
                        'starting_a1c' => $starting_a1c
                    ]);
                    break;
                default:
                    $result = $this->biometricUserService->addPatientSmoking($userId, $biometricId, [
                        'starting' => $starting,
                        'target' => $target
                    ]);
                    break;
            }
        } else {
            return $this->badRequest('"biometric_id" is important');
        }
        return response()->json($result);
    }
}
