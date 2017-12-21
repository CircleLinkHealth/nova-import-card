<?php

namespace App\Http\Controllers;

use App\AppConfig;
use App\User;
use App\Patient;
use App\Services\PatientService;
use App\Http\Controllers\Controller;
use App\Models\CCD\Problem;
use App\Models\CPM\CpmProblem;
use App\Models\ProblemCode;
use Carbon\Carbon;
use Illuminate\Http\Request;


class PatientController extends Controller
{
    private $patientService;
    /**
     * CpmProblemController constructor.
     *
     */
    public function __construct(PatientService $patientService)
    {   
        $this->$patientService = $patientService;
    }

    /**
     * returns a list of CPM Problems in the system
     */
    public function index()
    {
        return response()->json(null);
    }

    /**
    * note that the $patientId route parameter is actually userId
    */
    public function getPatient($patientId) {
        $user = User::find($patientId);
        $patient = $user->patientInfo()->get();
        return response()->json($patient);
    }
    
    public function getProblems($patientId)
    {
        $user = User::find($patientId);
        $mapTypeFn = function ($type) {
            return function ($problem) use ($type) {
                $problem['type'] = $type;
                return $problem;
            };
        };
        $cpmProblems = array_map($mapTypeFn('cpm'), $this->_getPatientCpmProblems($user, $patientId));
        $ccdProblems = array_map($mapTypeFn('ccd'), $this->_getPatientCcdProblems($user, $patientId));
        return response()->json(array_merge($cpmProblems, $ccdProblems));
    }
    
    public function getCpmProblems($patientId)
    {
        $user = User::find($patientId);
        $cpmProblems = $this->$patientService->getCpmProblems($patientId);
        return response()->json($cpmProblems);
    }
    
    public function getCcdProblems($patientId)
    {
        $user = User::find($patientId);
        $ccdProblems = $this->_getPatientCcdProblems($user);
        return response()->json($ccdProblems);
    }

    public function addCpmProblem(Request $request) {
        $patientId = $request->routes()->parameters()['patientId'];
        /** not complete */
    }

    /** begin private functions */

    function _getPatientCpmProblems($user, $patientId) {
        return $user->cpmProblems()->get()->map(function ($p) use ($patientId) {
            return [
                'id'   => $p->id,
                'name' => $p->name,
                'code' => $p->default_icd_10_code,
                'instructions' => $p->user()->where('patient_id', $patientId)->first()->instruction()->get()
            ];
        })->toArray();
    }
    
    function _getPatientCcdProblems($user) {
        return $user->ccdProblems()->get()->map(function ($p) {
            return [
                'id'   => $p->id,
                'name' => $p->name,
                'code' => $p->icd_10_code
            ];
        })->toArray();
    }
}
