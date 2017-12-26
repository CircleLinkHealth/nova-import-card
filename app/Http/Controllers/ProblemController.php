<?php

namespace App\Http\Controllers;

use App\AppConfig;
use App\Patient;
use App\Http\Controllers\Controller;
use App\Models\CCD\Problem;
use App\Models\CPM\CpmProblem;
use App\Services\CPM\CpmProblemService;
use App\Services\CCD\CcdProblemService;
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

    /**
     * ProblemController constructor.
     *
     */
    public function __construct(CpmProblemService $cpmProblemService, CcdProblemService $ccdProblemService, PatientService $patientService)
    {
        $this->cpmProblemService = $cpmProblemService;
        $this->ccdProblemService = $ccdProblemService;
        $this->patientService = $patientService;
    }

    public function index() {
        return response()->json([
            'cpm_count'   => $this->cpmProblemService->repo()->count(),
            'ccd_count'   => $this->ccdProblemService->repo()->count()
        ]);
    }

    public function cpmProblems() {
        return response()->json($this->cpmProblemService->problems());
    }

    public function ccdProblems() {
        $ccdProblems = $this->getCcdProblems();
        return response()->json($ccdProblems);
    }
    
    public function cpmProblem($cpmId) {
        $cpmProblem = $this->getCpmProblem($cpmId);
        if ($cpmProblem) return response()->json($cpmProblem);
        else return response()->json([
            'message' => 'not found'
        ], 404);
    }
    
    public function ccdProblem($ccdId) {
        $ccdProblem = $this->getCcdProblem($ccdId);
        if ($ccdProblem) return response()->json($ccdProblem);
        else return response()->json([
            'message' => 'not found'
        ], 404);
    }

    function getCpmProblem($id) {
        $problem = CpmProblem::where('id', $id)->first();
        if ($problem) return $this->setupCpmProblem($problem);
        else return null;
    }

    function getCcdProblems() {
        $problems = Problem::groupBy('name')->orderBy('id')->paginate(30);
        $problems->getCollection()->transform(function ($value) {
            return $this->setupCcdProblem($value);
        });
        return $problems;
    }
    
    function getCcdProblem($id) {
        $problem = Problem::where('id', $id)->first();
        if ($problem) return $this->setupCcdProblem($problem);
        else return null;
    }
}
