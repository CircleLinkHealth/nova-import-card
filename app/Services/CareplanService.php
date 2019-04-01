<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Repositories\CareplanRepository;
use App\Services\CCD\CcdAllergyService;
use App\Services\CCD\CcdProblemService;
use App\Services\CPM\CpmBiometricService;
use App\Services\CPM\CpmLifestyleService;
use App\Services\CPM\CpmMedicationGroupService;
use App\Services\CPM\CpmMedicationService;
use App\Services\CPM\CpmMiscService;
use App\Services\CPM\CpmProblemService;
use App\Services\CPM\CpmProblemUserService;
use App\Services\CPM\CpmSymptomService;
use CircleLinkHealth\Customer\Entities\User;

class CareplanService
{
    private $allergyService;
    private $appointmentService;
    private $biometricService;
    private $careplanRepo;
    private $ccdUserService;
    private $cpmService;
    private $cpmUserService;
    private $lifestyleService;
    private $medicationGroupService;
    private $medicationService;
    private $miscService;
    private $noteService;
    private $symptomService;

    public function __construct(
        CareplanRepository $careplanRepo,
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
                                NoteService $noteService
    ) {
        $this->careplanRepo           = $careplanRepo;
        $this->cpmService             = $cpmService;
        $this->cpmUserService         = $cpmUserService;
        $this->ccdUserService         = $ccdUserService;
        $this->medicationService      = $medicationService;
        $this->medicationGroupService = $medicationGroupService;
        $this->biometricService       = $biometricService;
        $this->symptomService         = $symptomService;
        $this->lifestyleService       = $lifestyleService;
        $this->allergyService         = $allergyService;
        $this->miscService            = $miscService;
        $this->appointmentService     = $appointmentService;
        $this->noteService            = $noteService;
    }

    public function careplan($userId)
    {
        $user = is_a($userId, User::class)
            ? $userId
            : User::findOrFail($userId);

        $user->loadMissing(['ccdProblems.cpmInstruction', 'ccdProblems.codes']);

        return [
            'allCpmProblems'   => $this->cpmService->all(),
            'cpmProblems'      => $this->cpmUserService->getPatientProblems($userId),
            'ccdProblems'      => $this->ccdUserService->getPatientProblemsValues($user),
            'medications'      => $this->medicationService->repo()->patientMedication($userId)->getCollection(),
            'medicationGroups' => $this->medicationGroupService->repo()->groups(),
            'healthGoals'      => $this->biometricService->patientBiometrics($userId),
            'baseHealthGoals'  => $this->biometricService->biometrics(),
            'symptoms'         => $this->symptomService->repo()->patientSymptoms($userId),
            'allSymptoms'      => $this->symptomService->repo()->symptoms()->getCollection(),
            'lifestyles'       => $this->lifestyleService->patientLifestyles($userId),
            'allLifestyles'    => $this->lifestyleService->repo()->lifestyles()->getCollection(),
            'allergies'        => $this->allergyService->patientAllergies($userId),
            'misc'             => $this->miscService->patientMisc($userId),
            'allMisc'          => $this->miscService->repo()->misc(),
            'appointments'     => $this->appointmentService->repo()->patientAppointments($userId)->getCollection(),
            'healthGoalNote'   => $this->noteService->patientBiometricNotes($userId)->first(),
        ];
    }

    public function repo()
    {
        return $this->careplanRepo;
    }
}
