<?php

namespace App\Http\Controllers;

use App\AppConfig;
use App\Patient;
use App\Http\Controllers\Controller;
use App\Models\CCD\Problem;
use App\Models\CPM\CpmProblem;
use App\Models\ProblemCode;
use Carbon\Carbon;
use Illuminate\Http\Request;


class ProblemController extends Controller
{
    /**
     * ProblemController constructor.
     *
     */
    public function __construct()
    {

    }

    public function cpmProblems() {
        $cpmProblems = $this->getCpmProblems();
        return response()->json($cpmProblems);
    }
    
    public function cpmProblem($cpmId) {
        $cpmProblem = $this->getCpmProblem($cpmId);
        if ($cpmProblem) return response()->json($cpmProblem);
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
    

    function getCpmProblems() {
        return CpmProblem::where('name', '!=', 'Diabetes')
                    ->get()
                    ->map([$this, 'setupCpmProblem']);
    }

    function getCpmProblem($id) {
        $problem = CpmProblem::where('id', $id)->first();
        if ($problem) return $this->setupCpmProblem($problem);
        else return null;
    }
}
