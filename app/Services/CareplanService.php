<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Models\CPM\CpmBiometric;
use App\Models\CPM\CpmLifestyle;
use App\Models\CPM\CpmMedicationGroup;
use App\Models\CPM\CpmMisc;
use App\Models\CPM\CpmSymptom;
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

    public function __construct(
        CareplanRepository $careplanRepo,
        CpmProblemService $cpmService,
        CpmProblemUserService $cpmUserService,
        CcdProblemService $ccdUserService,
        CpmMedicationService $medicationService,
        CpmMedicationGroupService $medicationGroupService,
        CpmBiometricService $biometricService,
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

        $user->loadMissing([
            'appointments' => function ($q) {
                $q->orderBy('id', 'desc')->paginate(5);
            },
            'carePlan',
            'carePlanAssessment' => function ($q) {
                $q->whereNotNull('key_treatment');
            },
            'ccdInsurancePolicies',
            'ccdAllergies',
            'ccdMedications',
            'ccdProblems.cpmInstruction',
            'ccdProblems.codes',
            'cpmMiscUserPivot.cpmInstruction',
            'cpmMiscUserPivot.cpmMisc',
            'cpmSymptoms',
            'cpmProblems',
            'cpmLifestyles',
            'cpmBiometrics',
            'cpmMedicationGroups',
        ]);

        return [
            'allCpmProblems'   => $this->cpmService->all(),
            'cpmProblems'      => $this->cpmUserService->getPatientProblems($userId),
            'ccdProblems'      => $this->ccdUserService->getPatientProblemsValues($user),
            'medications'      => $user->ccdMedications,
            'medicationGroups' => CpmMedicationGroup::get()->toArray(),
            'healthGoals'      => $this->biometricService->patientBiometrics($user),
            'baseHealthGoals'  => CpmBiometric::get(),
            'symptoms'         => $user->cpmSymptoms,
            'allSymptoms'      => CpmSymptom::get(),
            'lifestyles'       => $user->cpmLifestyles,
            'allLifestyles'    => CpmLifestyle::get(),
            'allergies'        => $this->allergyService->patientAllergies($userId),
            'misc'             => $this->miscService->patientMisc($userId),
            'allMisc'          => CpmMisc::get(),
            'appointments'     => $user->appointments,
            'healthGoalNote'   => $user->carePlanAssessment ? [
                'body                ' => $user->carePlanAssessment->key_treatment,
                'author_id           ' => $user->carePlanAssessment->provider_approver_id,
                'patient_id          ' => $user->carePlanAssessment->careplan_id,
                'created_at          ' => $user->carePlanAssessment->created_at,
                'updated_at          ' => $user->carePlanAssessment->updated_at,
                'isTCM               ' => 0,
                'did_medication_recon' => 0,
                'type                ' => 'Biometrics',
                'id                  ' => 0,
            ] : [],
        ];
    }

    public function repo()
    {
        return $this->careplanRepo;
    }
}
