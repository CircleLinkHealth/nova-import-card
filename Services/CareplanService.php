<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Services;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Services\NoteService;
use CircleLinkHealth\SharedModels\Entities\CpmBiometric;
use CircleLinkHealth\SharedModels\Entities\CpmLifestyle;
use CircleLinkHealth\SharedModels\Entities\CpmMedicationGroup;
use CircleLinkHealth\SharedModels\Entities\CpmMisc;
use CircleLinkHealth\SharedModels\Entities\CpmSymptom;
use CircleLinkHealth\SharedModels\Entities\Note;
use CircleLinkHealth\SharedModels\Entities\ProblemCodeSystem;
use CircleLinkHealth\SharedModels\Repositories\CareplanRepository;
use CircleLinkHealth\SharedModels\Services\CCD\CcdAllergyService;
use CircleLinkHealth\SharedModels\Services\CCD\CcdProblemService;
use CircleLinkHealth\SharedModels\Services\CPM\CpmBiometricService;
use CircleLinkHealth\SharedModels\Services\CPM\CpmLifestyleService;
use CircleLinkHealth\SharedModels\Services\CPM\CpmMedicationGroupService;
use CircleLinkHealth\SharedModels\Services\CPM\CpmMedicationService;
use CircleLinkHealth\SharedModels\Services\CPM\CpmMiscService;
use CircleLinkHealth\SharedModels\Services\CPM\CpmProblemUserService;

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
            'allCpmProblems'     => $this->cpmService->all(),
            'cpmProblems'        => $this->cpmUserService->getPatientProblems($userId),
            'ccdProblems'        => $this->ccdUserService->getPatientProblemsValues($user),
            'medications'        => $user->ccdMedications,
            'medicationGroups'   => CpmMedicationGroup::get()->toArray(),
            'healthGoals'        => $this->biometricService->patientBiometrics($user),
            'baseHealthGoals'    => CpmBiometric::get(),
            'symptoms'           => $user->cpmSymptoms,
            'allSymptoms'        => CpmSymptom::get(),
            'lifestyles'         => $user->cpmLifestyles,
            'allLifestyles'      => CpmLifestyle::get(),
            'allergies'          => $this->allergyService->patientAllergies($userId),
            'misc'               => $this->miscService->patientMisc($userId),
            'allMisc'            => CpmMisc::get(),
            'appointments'       => $user->appointments,
            'healthGoalNote'     => $this->healthGoalNote($user),
            'allCpmProblemCodes' => ProblemCodeSystem::get(),
        ];
    }

    public function repo()
    {
        return $this->careplanRepo;
    }

    private function healthGoalNote(User $user)
    {
        $assessment = $user->carePlanAssessment;

        if ( ! $assessment) {
            return null;
        }

        $note                       = new Note();
        $note->body                 = $assessment->key_treatment;
        $note->author_id            = $assessment->provider_approver_id;
        $note->patient_id           = $assessment->careplan_id;
        $note->created_at           = $assessment->created_at;
        $note->updated_at           = $assessment->updated_at;
        $note->isTCM                = 0;
        $note->did_medication_recon = 0;
        $note->type                 = 'Biometrics';
        $note->id                   = 0;

        return $note;
    }
}
