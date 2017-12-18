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


class CpmProblemController extends Controller
{
    /**
     * CpmProblemController constructor.
     *
     */
    public function __construct()
    {

    }

    /**
     * returns a list of CPM Problems in the system
     */
    public function index()
    {
        $cpmProblems = CpmProblem::where('name', '!=', 'Diabetes')
                                 ->get()
                                 ->map(function ($p) {
                                     return [
                                         'id'   => $p->id,
                                         'name' => $p->name,
                                         'code' => $p->default_icd_10_code,
                                     ];
                                 });
        return response()->json($cpmProblems);
    }
    
    /**
        * returns a list of CPM Problems in the system
        */
    public function getPatientProblems($patientId)
    {
        $patient = Patient::find('patientId', $patientId)->get();
        if ($patient) {
            $cpmProblems = $patient->user()->cpmProblems()->get()->map(function ($p) {
                return [
                    'id'   => $p->id,
                    'name' => $p->name,
                    'code' => $p->default_icd_10_code,
                ];
            });
            return response()->json($cpmProblems);
        }
        else {
            return response()->json([
                'message' => 'could not find patient with id ' . $patientId
            ], 404);
        }
    }
}
