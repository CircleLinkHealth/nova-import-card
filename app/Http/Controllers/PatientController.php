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
use App\Services\CPM\CpmLifestyleService;
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
    private $lifestyleService;

    /**
     * CpmProblemController constructor.
     *
     */
    public function __construct(PatientService $patientService, 
                                CpmProblemUserService $cpmProblemUserService, 
                                CpmBiometricService $biometricUserService,
                                CpmMedicationService $medicationService,
                                CpmMedicationGroupService $medicationGroupService,
                                CpmSymptomService $symptomService,
                                CpmLifestyleService $lifestyleService)
    {   
        $this->patientService = $patientService;
        $this->cpmProblemUserService = $cpmProblemUserService;
        $this->biometricUserService = $biometricUserService;
        $this->medicationService = $medicationService;
        $this->medicationGroupService = $medicationGroupService;
        $this->symptomService = $symptomService;
        $this->lifestyleService = $lifestyleService;
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

    public function addMedication($userId, Request $request) {
        if ($userId) {
            $medication = new \App\Models\CCD\Medication();
            $medication->medication_import_id = $request->input('medication_import_id');
            $medication->ccda_id = $request->input('ccda_id');
            $medication->vendor_id = $request->input('vendor_id');
            $medication->ccd_medication_log_id = $request->input('ccd_medication_log_id');
            $medication->medication_group_id = $request->input('medication_group_id');
            $medication->name = $request->input('name');
            $medication->sig = $request->input('sig');
            $medication->code = $request->input('code');
            $medication->code_system = $request->input('code_system');
            $medication->code_system_name = $request->input('code_system_name');
            $medication->patient_id = $userId;
            return $this->medicationService->repo()->addMedicationToPatient($medication);
        }
        return $this->badRequest('"userId" is important');
    }
    
    public function removeMedication($userId, $medicationId) {
        if ($userId) {
            return $this->medicationService->repo()->removeMedicationFromPatient($medicationId, $userId);
        }
        return $this->badRequest('"userId" is important');
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

    public function getLifestyles($userId) {
        if ($userId) {
            return $this->lifestyleService->patientLifestyles($userId);
        }
        else return $this->badRequest('"userId" is important');
    }

    public function addLifestyle($userId, Request $request) {
        $lifestyleId = $request->input('lifestyleId');
        if ($userId && $lifestyleId) {
            return $this->lifestyleService->addLifestyleToPatient($lifestyleId, $userId);
        }
        else return $this->badRequest('"lifestyleId" and "userId" are important');
    }
    
    public function removeLifestyle($userId, $lifestyleId) {
        if ($userId && $lifestyleId) {
            return $this->lifestyleService->removeLifestyleFromPatient($lifestyleId, $userId);
        }
        else return $this->badRequest('"lifestyleId" and "userId" are important');
    }
}
