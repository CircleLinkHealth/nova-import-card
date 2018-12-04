<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Services\CPM\CpmBiometricService;

class BiometricController extends Controller
{
    private $biometricService;

    /**
     * BiometricController constructor.
     */
    public function __construct(CpmBiometricService $biometricService)
    {
        $this->biometricService = $biometricService;
    }

    public function index()
    {
        return response()->json($this->biometricService->biometrics());
    }

    public function patients($biometricId)
    {
        return response()->json($this->biometricService->biometricPatients($biometricId));
    }

    public function show($biometricId)
    {
        return response()->json($this->biometricService->biometric($biometricId));
    }
}
