<?php namespace App\Services;

use App\CarePlan;
use App\Services\CPM\CpmProblemService;
use App\Services\CCD\CcdProblemService;
use App\Services\CPM\CpmProblemUserService;
use App\Services\CPM\CpmMedicationService;
use App\Services\CPM\CpmMedicationGroupService;
use App\Services\CPM\CpmBiometricService;
use App\Services\CPM\CpmSymptomService;
use App\Services\CPM\CpmLifestyleService;
use App\Services\CCD\CcdAllergyService;
use App\Services\CPM\CpmMiscService;
use App\Services\NoteService;
use App\Services\AppointmentService;
use App\Repositories\CareplanRepository;

class CareplanService
{
    private $careplanRepo;
    private $cpmService;
    private $cpmUserService;
    private $ccdUserService;
    private $medicationService;
    private $medicationGroupService;
    private $biometricService;
    private $symptomService;
    private $lifestyleService;
    private $allergyService;
    private $miscService;
    private $appointmentService;
    private $noteService;

    public function __construct(CareplanRepository $careplanRepo, 
                                CpmProblemService $cpmService, 
                                CpmProblemUserService $cpmUserService, 
                                CcdProblemService $ccdUserService, 
                                CpmMedicationService $medicationService, 
                                CpmMedicationGroupService $medicationGroupService,
                                CpmBiometricService $biometricService,
                                CpmSymptomService $symptomService,
                                CpmLifestyleService $lifestyleService,
                                CcdAllergyService $allergyService,
                                CpmMiscService $miscService,
                                AppointmentService $appointmentService,
                                NoteService $noteService) {
        $this->careplanRepo = $careplanRepo;
        $this->cpmService = $cpmService;
        $this->cpmUserService = $cpmUserService;
        $this->ccdUserService = $ccdUserService;
        $this->medicationService = $medicationService;
        $this->medicationGroupService = $medicationGroupService;
        $this->biometricService = $biometricService;
        $this->symptomService = $symptomService;
        $this->lifestyleService = $lifestyleService;
        $this->allergyService = $allergyService;
        $this->miscService = $miscService;
        $this->appointmentService = $appointmentService;
        $this->noteService = $noteService;
    }

    public function repo() {
        return $this->careplanRepo;
    }

    public function careplan($userId) {
        return [
            'allCpmProblems'    => $this->cpmService->problems()->getCollection(),
            'cpmProblems'       => $this->cpmUserService->getPatientProblems($userId),
            'ccdProblems'       => $this->ccdUserService->getPatientProblems($userId),
            'medications'       => $this->medicationService->repo()->patientMedication($userId)->getCollection(),
            'medicationGroups'  => $this->medicationGroupService->repo()->groups(),
            'healthGoals'       => $this->biometricService->patientBiometrics($userId),
            'baseHealthGoals'   => $this->biometricService->biometrics(),
            'symptoms'          => $this->symptomService->repo()->patientSymptoms($userId),
            'allSymptoms'       => $this->symptomService->repo()->symptoms()->getCollection(),
            'lifestyles'        => $this->lifestyleService->patientLifestyles($userId),
            'allLifestyles'     => $this->lifestyleService->repo()->lifestyles()->getCollection(),
            'allergies'         => $this->allergyService->patientAllergies($userId),
            'misc'              => $this->miscService->patientMisc($userId),
            'allMisc'           => $this->miscService->repo()->misc(),
            'appointments'      => $this->appointmentService->repo()->patientAppointments($userId)->getCollection(),
            'healthGoalNote'    => $this->noteService->repo()->patientNotes($userId, 'Biometrics')->getCollection()->first()
        ];
    }
}
