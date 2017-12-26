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
            'ccd_count'   => $this->ccdProblemService->repo()->count() //Problem::select('name', DB::raw('count(*) as total'))->groupBy('name')->pluck('total')->count()
        ]);
    }

    public function cpmProblems() {
        $cpmProblems = $this->getCpmProblems();
        return response()->json($cpmProblems);
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
    
    /**
    * private functions
    */
    function setupCpmProblem($p) {
        return [
            'id'   => $p->id,
            'name' => $p->name,
            'code' => $p->default_icd_10_code,
            'instructions' => $p->instructions()->get()
        ];
    }
    
    function setupCcdProblem($p) {
        return [
            'id'    => $p->id,
            'name'  => $p->name,
            'cpm_id'  => $p->cpm_problem_id,
            'patients' => Problem::where('name', $p->name)->get([ 'patient_id' ])->map(function ($item) {
                return $item->patient_id;
            })
        ];
    }

    function getCpmProblems() {
        $problems = CpmProblem::where('name', '!=', 'Diabetes')
                    ->paginate(30);
        $problems->getCollection()->transform(function ($value) {
            return $this->setupCpmProblem($value);
        });
        return $problems;
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
