<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ApiPatient\Http\Controllers;

use App\Services\CPM\CpmBiometricService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UserBiometricController extends Controller
{
    /**
     * @var CpmBiometricService
     */
    protected $biometricUserService;

    /**
     * UserBiometricController constructor.
     */
    public function __construct(CpmBiometricService $biometricUserService)
    {
        $this->biometricUserService = $biometricUserService;
    }

    public function destroy($userId, $id)
    {
        return response()->json($this->biometricUserService->removePatientBiometric($userId, $id));
    }

    public function show($userId)
    {
        return response()->json($this->biometricUserService->patientBiometrics($userId));
    }

    public function store($userId, Request $request)
    {
        $biometricId             = $request->input('biometric_id');
        $starting                = $request->input('starting');
        $target                  = $request->input('target');
        $systolic_high_alert     = $request->input('systolic_high_alert');
        $systolic_low_alert      = $request->input('systolic_low_alert');
        $diastolic_high_alert    = $request->input('diastolic_high_alert');
        $diastolic_low_alert     = $request->input('diastolic_low_alert');
        $high_alert              = $request->input('high_alert');
        $low_alert               = $request->input('low_alert');
        $starting_a1c            = $request->input('starting_a1c');
        $monitor_changes_for_chf = $request->input('monitor_changes_for_chf');
        $result                  = null;
        if ($biometricId) {
            switch ($biometricId) {
                case 1:
                    $result = $this->biometricUserService->addPatientWeight($userId, $biometricId, [
                        'starting'                => null === $starting ? '' : $starting,
                        'target'                  => $target,
                        'monitor_changes_for_chf' => $monitor_changes_for_chf,
                    ]);
                    break;
                case 2:
                    $result = $this->biometricUserService->addPatientBloodPressure($userId, $biometricId, [
                        'starting'             => null === $starting ? '' : $starting,
                        'target'               => $target,
                        'diastolic_high_alert' => $diastolic_high_alert,
                        'diastolic_low_alert'  => $diastolic_low_alert,
                        'systolic_high_alert'  => $systolic_high_alert,
                        'systolic_low_alert'   => $systolic_low_alert,
                    ]);
                    break;
                case 3:
                    $result = $this->biometricUserService->addPatientBloodSugar($userId, $biometricId, [
                        'starting'     => null === $starting ? '' : $starting,
                        'target'       => $target,
                        'high_alert'   => $high_alert,
                        'low_alert'    => $low_alert,
                        'starting_a1c' => $starting_a1c,
                    ]);
                    break;
                default:
                    $result = $this->biometricUserService->addPatientSmoking($userId, $biometricId, [
                        'starting' => null === $starting ? '' : $starting,
                        'target'   => $target,
                    ]);
                    break;
            }
        } else {
            return \response('"biometric_id" is important');
        }

        return response()->json($result);
    }
}
