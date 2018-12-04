<?php

namespace App\Http\Controllers;

use App\AppConfig;
use App\Patient;
use App\Http\Controllers\Controller;
use App\Models\CCD\Problem;
use App\Models\CPM\CpmProblem;
use App\Services\CPM\CpmProblemService;
use App\Services\CCD\CcdProblemService;
use App\Services\CCD\CcdAllergyService;
use App\Services\PatientService;
use App\Models\ProblemCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProblemController extends Controller
{
    private $patientService;
    private $cpmProblemService;
    private $ccdProblemService;
    private $allergyService;

    /**
     * ProblemController constructor.
     *
     */
    public function __construct(
        CpmProblemService $cpmProblemService,
        CcdProblemService $ccdProblemService,
                                    PatientService $patientService,
        CcdAllergyService $allergyService
    ) {
        $this->cpmProblemService = $cpmProblemService;
        $this->ccdProblemService = $ccdProblemService;
        $this->patientService = $patientService;
        $this->allergyService = $allergyService;
    }

    public function index()
    {
        return response()->json([
            'cpm_count'   => $this->cpmProblemService->repo()->count(),
            'ccd_count'   => $this->ccdProblemService->repo()->count()
        ]);
    }

    public function cpmProblems()
    {
        return response()->json($this->cpmProblemService->problems());
    }

    public function ccdProblems()
    {
        return response()->json($this->ccdProblemService->problems());
    }
    
    public function ccdAllergies()
    {
        return response()->json($this->allergyService->allergies());
    }
    
    public function searchCcdAllergies(Request $request)
    {
        $term = $request->input('term');
        if ($term) {
            $terms = explode(',', $term);
            return response()->json($this->allergyService->searchAllergies($terms));
        } else {
            return $this->badRequest('missing parameter: "term"');
        }
    }
    
    public function cpmProblem($cpmId)
    {
        $cpmProblem = $this->cpmProblemService->problem($cpmId);
        if ($cpmProblem) {
            return response()->json($cpmProblem);
        } else {
            return response()->json([
            'message' => 'not found'
        ], 404);
        }
    }
    
    public function ccdProblem($ccdId)
    {
        $ccdProblem = $this->ccdProblemService->problem($ccdId);
        if ($ccdProblem) {
            return response()->json($ccdProblem);
        } else {
            return response()->json([
            'message' => 'not found'
        ], 404);
        }
    }
}
