<?php

namespace App\Http\Controllers\API;

use App\Services\PatientService;
use App\Services\AppointmentService;
use Illuminate\Http\Request;

class AppointmentController extends ApiController
{
    private $patientService;
    private $appointmentService;

    public function __construct(PatientService $patientService, AppointmentService $appointmentService)
    {
        $this->patientService = $patientService;
        $this->appointmentService = $appointmentService;
    }

    public function index()
    {
        return response()->json($this->appointmentService->appointments());
    }

    public function show($id)
    {
        return response()->json($this->appointmentService->repo()->appointment($id));
    }
}
