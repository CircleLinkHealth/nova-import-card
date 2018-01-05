<?php

namespace App\Http\Controllers;

use App\AppConfig;
use App\User;
use App\Patient;
use App\Services\PatientService;
use App\Services\CPM\CpmProblemUserService;
use App\Services\CPM\CpmBiometricService;
use App\Services\CPM\CpmMedicationService;
use App\Services\CPM\CpmMedicationGroupService;
use App\Services\CPM\CpmSymptomService;
use App\Http\Controllers\Controller;
use App\Models\CCD\Problem;
use App\Models\CPM\CpmProblem;
use App\Models\ProblemCode;
use Carbon\Carbon;
use Illuminate\Http\Request;


class PatientController extends Controller
{
    private $patientService;
    private $cpmProblemUserService;
    private $biometricUserService;
    private $medicationService;
    private $medicationGroupService;
    private $symptomService;

    /**
     * CpmProblemController constructor.
     *
     */
    public function __construct(PatientService $patientService, 
                                CpmProblemUserService $cpmProblemUserService, 
                                CpmBiometricService $biometricUserService,
                                CpmMedicationService $medicationService,
                                CpmMedicationGroupService $medicationGroupService,
                                CpmSymptomService $symptomService)
    {   
        $this->patientService = $patientService;
        $this->cpmProblemUserService = $cpmProblemUserService;
        $this->biometricUserService = $biometricUserService;
        $this->medicationService = $medicationService;
        $this->medicationGroupService = $medicationGroupService;
        $this->symptomService = $symptomService;
    }

    /**
     * returns a list of CPM Problems in the system
     */
    public function index()
    {
        return response()->json(null);
    }

    public function getPatient($userId) {
        return response()->json($this->patientService->getPatientByUserId($userId));
    }
    
    public function getProblems($userId)
    {
        $cpmProblems = array_map($this->patientService->mapTypeFn('cpm'), $this->patientService->getCpmProblems($userId));
        $ccdProblems = array_map($this->patientService->mapTypeFn('ccd'), $this->patientService->getCcdProblems($userId));
        return response()->json(array_merge($cpmProblems, $ccdProblems));
    }
    
    public function getCpmProblems($userId)
    {
        return response()->json($this->patientService->getCpmProblems($userId));
    }
    
    public function getCcdProblems($userId)
    {
        return response()->json($this->patientService->getCcdProblems($userId));
    }
    
    public function getCcdAllergies($userId)
    {
        return response()->json($this->patientService->getCcdAllergies($userId));
    }
    
    public function getBiometrics($userId)
    {
        return response()->json($this->biometricUserService->patientBiometrics($userId));
    }

    public function addBiometric($userId, $biometricId) {
        
    }

    public function addCpmProblem($userId, Request $request) {
        $cpmProblemId = $request->input('cpmProblemId');
        if ($userId && $cpmProblemId) {
            $this->cpmProblemUserService->addProblemToPatient($userId, $cpmProblemId);
            return $this->getCpmProblems($userId);
        }
        return $this->badRequest('"userId" and "cpmProblemId" are important');
    }
    
    public function removeCpmProblem($userId, $cpmId) {
        if ($userId && $cpmId) {
            $this->cpmProblemUserService->removeProblemFromPatient($userId, $cpmId);
            return $this->getCpmProblems($userId);
        }
        return $this->badRequest('"userId" and "cpmId" are important');
    }
    
    public function getMedication($userId) {
        if ($userId) {
            return $this->medicationService->repo()->patientMedication($userId);
        }
        return $this->badRequest('"userid" is important');
    } 

    public function getMedicationGroups($userId) {
        if ($userId) {
            return $this->medicationGroupService->repo()->patientGroups($userId);
        }
        return $this->badRequest('"userid" is important');
    }

    public function getSymptoms($userId) {
        if ($userId) {
            return $this->symptomService->repo()->patientSymptoms($userId);
        }
        return $this->badRequest('"userId" is important');
    }

    public function addSymptom($userId, Request $request) {
        $symptomId = $request->input('symptomId');
        if ($userId && $symptomId) {
            return $this->symptomService->repo()->addSymptomToPatient($symptomId, $userId);
        }
        else return $this->badRequest('"symptomId" and "userId" are important');
    }
    
    public function removeSymptom($userId, $symptomId) {
        if ($userId && $symptomId) {
            $result = $this->symptomService->repo()->removeSymptomFromPatient($symptomId, $userId);
            return $result ? response()->json($result) : $this->notFound('provided patient does not have the symptom in question');
        }
        else return $this->badRequest('"symptomId" and "userId" are important');
    }
}
