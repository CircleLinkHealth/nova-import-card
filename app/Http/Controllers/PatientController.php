<?php

namespace App\Http\Controllers;

use App\AppConfig;
use App\User;
use App\Patient;
use App\Appointment;
use App\Services\NoteService;
use App\Services\PatientService;
use App\Services\AppointmentService;
use App\Services\CCD\CcdAllergyService;
use App\Services\CCD\CcdProblemService;
use App\Services\CPM\CpmProblemUserService;
use App\Services\CPM\CpmBiometricService;
use App\Services\CPM\CpmMedicationService;
use App\Services\CPM\CpmMedicationGroupService;
use App\Services\CPM\CpmSymptomService;
use App\Services\CPM\CpmLifestyleService;
use App\Services\CPM\CpmMiscService;
use App\Http\Controllers\Controller;
use App\Models\CCD\Problem;
use App\Models\CPM\CpmProblem;
use App\Models\ProblemCode;
use Carbon\Carbon;
use Illuminate\Http\Request;


class PatientController extends Controller
{
    private $patientService;
    private $appointmentService;
    private $allergyService;
    private $ccdProblemService;
    private $cpmProblemUserService;
    private $biometricUserService;
    private $medicationService;
    private $medicationGroupService;
    private $symptomService;
    private $lifestyleService;
    private $miscService;
    private $noteService;

    /**
     * CpmProblemController constructor.
     *
     */
    public function __construct(PatientService $patientService, 
                                AppointmentService $appointmentService,
                                CcdAllergyService $allergyService,
                                CcdProblemService $ccdProblemService,
                                CpmProblemUserService $cpmProblemUserService, 
                                CpmBiometricService $biometricUserService,
                                CpmMedicationService $medicationService,
                                CpmMedicationGroupService $medicationGroupService,
                                CpmSymptomService $symptomService,
                                CpmLifestyleService $lifestyleService,
                                CpmMiscService $miscService,
                                NoteService $noteService)
    {   
        $this->patientService = $patientService;
        $this->appointmentService = $appointmentService;
        $this->allergyService = $allergyService;
        $this->ccdProblemService = $ccdProblemService;
        $this->cpmProblemUserService = $cpmProblemUserService;
        $this->biometricUserService = $biometricUserService;
        $this->medicationService = $medicationService;
        $this->medicationGroupService = $medicationGroupService;
        $this->symptomService = $symptomService;
        $this->lifestyleService = $lifestyleService;
        $this->miscService = $miscService;
        $this->noteService = $noteService;
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
    
    public function removeCcdProblem($userId, $ccdId)
    {
        if ($userId && $ccdId) {
            return response()->json($this->ccdProblemService->repo()->removePatientCcdProblem($userId, $ccdId));
        }
        else return $this->badRequest('"userId" and "ccdId" are important');
    }
    
    public function addCcdProblem($userId, Request $request)
    {
        $name = $request->input('name');
        if ($userId && $name) {
            return response()->json($this->ccdProblemService->repo()->addPatientCcdProblem($userId, $name));
        }
        else return $this->badRequest('"userId" and "name" are important');
    }
    
    public function getCcdAllergies($userId)
    {
        return response()->json($this->patientService->getCcdAllergies($userId));
    }
    
    public function addCcdAllergies($userId, Request $request)
    {
        $name = $request->input('name');
        if ($name) {
            return response()->json($this->allergyService->addPatientAllergy($userId, $name));
        }
        else return $this->badRequest('"name" is important');
    }
    
    public function deleteCcdAllergy($userId, $allergyId)
    {
        if ($userId && $allergyId) {
            return response()->json($this->allergyService->deletePatientAllergy($userId, $allergyId));
        }
        else return $this->badRequest('"userId" and "allergyId" are important');
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

    function retrieveMedication(Request $request) {
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
        return $medication;
    }

    public function editMedication($userId, $id, Request $request) {
        if ($userId) {
            $medication = $this->retrieveMedication($request);
            $medication->id = $id;
            $medication->patient_id = $userId;
            return $this->medicationService->editPatientMedication($medication);
        }
        return $this->badRequest('"userId" is important');
    }

    public function addMedication($userId, Request $request) {
        if ($userId) {
            $medication = $this->retrieveMedication($request);
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
    
    public function getMisc($userId) {
        if ($userId) {
            return $this->miscService->patientMisc($userId);
        }
        else return $this->badRequest('"userId" is important');
    }
    
    public function getMiscByType($userId, $miscTypeId) {
        if ($userId) {
            return $this->miscService->patientMiscByType($userId, $miscTypeId);
        }
        else return $this->badRequest('"userId" is important');
    }

    public function addMisc($userId, Request $request) {
        $miscId = $request->input('miscId');
        if ($userId && $miscId) {
            return $this->miscService->addMiscToPatient($miscId, $userId);
        }
        else return $this->badRequest('"miscId" and "userId" are important');
    }
    
    public function removeMisc($userId, $miscId) {
        if ($userId && $miscId) {
            return $this->miscService->removeMiscFromPatient($miscId, $userId);
        }
        else return $this->badRequest('"miscId" and "userId" are important');
    }
    
    public function addInstructionToMisc($userId, $miscId, Request $request) {
        $instructionId = $request->input('instructionId');
        if ($userId && $miscId && $instructionId) {
            return $this->miscService->editPatientMisc($miscId, $userId, $instructionId);
        }
        else return $this->badRequest('"miscId", "userId" and "instructionId" are important');
    }
    
    public function removeInstructionFromMisc($userId, $miscId, $instructionId) {
        if ($userId && $miscId && $instructionId) {
            return $this->miscService->removeInstructionFromPatientMisc($miscId, $userId, $instructionId);
        }
        else return $this->badRequest('"miscId", "userId" and "instructionId" are important');
    }

    public function getNotes($userId) {
        if ($userId) {
            return $this->noteService->repo()->patientNotes($userId);
        }
        else return $this->badRequest('"userId" is important');
    }
    
    public function addNote($userId, Request $request) {
        $body = $request->input('body');
        $author_id = auth()->user()->id;
        $type = $request->input('type');
        $isTCM = $request->input('isTCM') ?? 0;
        $did_medication_recon = $request->input('did_medication_recon') ?? 0;
        if ($userId && $body && $author_id) {
            return $this->noteService->add($userId, $author_id, $body, $type, $isTCM, $did_medication_recon);
        }
        else return $this->badRequest('"userId" and "body" and "author_id" are important');
    }
    
    public function editNote($userId, $id, Request $request) {
        $body = $request->input('body');
        $author_id = auth()->user()->id;
        $isTCM = $request->input('isTCM') ?? 0;
        $did_medication_recon = $request->input('did_medication_recon') ?? 0;
        if ($userId && $id && $author_id) {
            return $this->noteService->editPatientNote($id, $userId, $author_id, $body, $isTCM, $did_medication_recon);
        }
        else return $this->badRequest('"userId", "author_id" and "noteId" are is important');
    }

    public function addAppointment($userId, Request $request) {
        $appointment = new Appointment();
        $appointment->comment = $request->input('comment');
        $appointment->patient_id = $userId;
        $appointment->author_id = auth()->user()->id;
        $appointment->type = $request->input('type');
        $appointment->provider_id = $request->input('provider_id');
        $appointment->date = $request->input('date');
        $appointment->time = $request->input('time');
        if ($userId && $appointment->provider_id && $appointment->author_id && $appointment->type && $appointment->comment) {
            return response()->json($this->appointmentService->repo()->create($appointment));
        }
        else return $this->badRequest('"userId", "author_id", "type", "comment" and "provider_id" are is important');
    }

    public function getAppointments($userId) {
        return response()->json($this->appointmentService->repo()->patientAppointments($userId));
    }

    public function removeAppointment($userId, $id) {
        return response()->json($this->appointmentService->removePatientAppointment($userId, $id));
    }
}
