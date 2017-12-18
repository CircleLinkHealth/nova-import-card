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
        return response()->json(null);
    }

    public function getPatient($patientId) {
        $patient = Patient::where('id', $patientId)->first();
        return response()->json($patient);
    }
    
    public function getProblems($patientId)
    {
        $patient = Patient::where('id', $patientId)->first();
        $mapTypeFn = function ($type) {
            return function ($problem) use ($type) {
                $problem['type'] = $type;
                return $problem;
            };
        };
        $cpmProblems = array_map($mapTypeFn('cpm'), $this->_getPatientCpmProblems($patient));
        $ccdProblems = array_map($mapTypeFn('ccd'), $this->_getPatientCcdProblems($patient));
        return response()->json(array_merge($cpmProblems, $ccdProblems));
    }
    
    public function getCpmProblems($patientId)
    {
        $patient = Patient::where('id', $patientId)->first();
        $cpmProblems = $this->_getPatientCpmProblems($patient);
        return response()->json($cpmProblems);
    }
    
    public function getCcdProblems($patientId)
    {
        $patient = Patient::where('id', $patientId)->first();
        $ccdProblems = $this->_getPatientCcdProblems($patient);
        return response()->json($ccdProblems);
    }

    /** begin private functions */

    function _getPatientCpmProblems($patient) {
        return $patient->user()->first()->cpmProblems()->get()->map(function ($p) {
            return [
                'id'   => $p->id,
                'name' => $p->name,
                'code' => $p->default_icd_10_code
            ];
        })->toArray();
    }
    
    function _getPatientCcdProblems($patient) {
        return $patient->user()->first()->ccdProblems()->get()->map(function ($p) {
            return [
                'id'   => $p->id,
                'name' => $p->name,
                'code' => $p->icd_10_code
            ];
        })->toArray();
    }
}
