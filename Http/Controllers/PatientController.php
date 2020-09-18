<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ApiPatient\Http\Controllers;

use CircleLinkHealth\Customer\Filters\PatientFilters;
use App\Http\Controllers\Controller;
use App\Services\PatientService;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    private $patientService;

    /**
     * CpmProblemController constructor.
     */
    public function __construct(
        PatientService $patientService
    ) {
        $this->patientService = $patientService;
    }

    /**
     * returns a list of CPM Problems in the system.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function index(PatientFilters $filters)
    {
        return $this->patientService->patients($filters);
    }

    public function show($userId)
    {
        return response()->json($this->patientService->getPatientByUserId($userId));
    }
}
