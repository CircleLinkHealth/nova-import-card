<?php

namespace App\Http\Controllers;

use App\AppConfig;
use App\Services\CPM\CpmBiometricService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BiometricController extends Controller
{
    private $biometricService;

    /**
     * BiometricController constructor.
     *
     */
    public function __construct(CpmBiometricService $biometricService)
    {
        $this->biometricService = $biometricService;
    }

    public function index()
    {
        return response()->json($this->biometricService->biometrics());
    }
    
    public function show($biometricId)
    {
        return response()->json($this->biometricService->biometric($biometricId));
    }
    
    public function patients($biometricId)
    {
        return response()->json($this->biometricService->biometricPatients($biometricId));
    }
}
