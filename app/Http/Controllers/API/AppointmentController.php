<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

use CircleLinkHealth\SharedModels\Services\AppointmentService;
use CircleLinkHealth\SharedModels\Services\PatientService;

class AppointmentController extends ApiController
{
    private $appointmentService;
    private $patientService;

    public function __construct(PatientService $patientService, AppointmentService $appointmentService)
    {
        $this->patientService     = $patientService;
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
