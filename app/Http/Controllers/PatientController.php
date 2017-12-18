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


class PatientController extends Controller
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

    public function getPatient($patientId) {
        $patient = Patient::where('id', $patientId)->first();
        if ($patient) {
            return response()->json($patient);
        }
        else {
            return response()->json([
                'message' => 'could not find patient with id ' . $patientId
            ], 404);
        }
    }
    
    /**
        * 
        */
    public function getProblems($patientId)
    {
        $patient = Patient::where('id', $patientId)->first();
        if ($patient) {
            $cpmProblems = $patient->user()->first()->cpmProblems()->get()->map(function ($p) {
                return [
                    'id'   => $p->id,
                    'name' => $p->name,
                    'code' => $p->default_icd_10_code
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
