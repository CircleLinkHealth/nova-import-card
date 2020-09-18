<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Services\CCD\CcdAllergyService;
use App\Services\CCD\CcdProblemService;
use CircleLinkHealth\SharedModels\Services\CpmProblemService;
use App\Services\PatientService;
use Illuminate\Http\Request;

class ProblemController extends Controller
{
    private $allergyService;
    private $ccdProblemService;
    private $cpmProblemService;
    private $patientService;

    /**
     * ProblemController constructor.
     */
    public function __construct(
        CpmProblemService $cpmProblemService,
        CcdProblemService $ccdProblemService,
        PatientService $patientService,
        CcdAllergyService $allergyService
    ) {
        $this->cpmProblemService = $cpmProblemService;
        $this->ccdProblemService = $ccdProblemService;
        $this->patientService    = $patientService;
        $this->allergyService    = $allergyService;
    }

    public function ccdAllergies()
    {
        return response()->json($this->allergyService->allergies());
    }

    public function ccdProblem($ccdId)
    {
        $ccdProblem = $this->ccdProblemService->problem($ccdId);
        if ($ccdProblem) {
            return response()->json($ccdProblem);
        }

        return response()->json([
            'message' => 'not found',
        ], 404);
    }

    public function ccdProblems()
    {
        return response()->json($this->ccdProblemService->problems());
    }

    public function cpmProblem($cpmId)
    {
        $cpmProblem = $this->cpmProblemService->problem($cpmId);
        if ($cpmProblem) {
            return response()->json($cpmProblem);
        }

        return response()->json([
            'message' => 'not found',
        ], 404);
    }

    public function cpmProblems()
    {
        return response()->json($this->cpmProblemService->problems());
    }

    public function searchCcdAllergies(Request $request)
    {
        $term = $request->input('term');
        if ($term) {
            $terms = explode(',', $term);

            return response()->json($this->allergyService->searchAllergies($terms));
        }

        return $this->badRequest('missing parameter: "term"');
    }
}
