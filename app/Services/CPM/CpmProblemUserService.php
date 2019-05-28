<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\CPM;

use App\Models\CPM\CpmInstruction;
use App\Repositories\CpmProblemUserRepository;
use App\Repositories\UserRepositoryEloquent;
use CircleLinkHealth\Customer\Entities\User;

class CpmProblemUserService
{
    private $cpmProblemService;
    private $cpmProblemUserRepo;
    private $userRepo;

    public function __construct(CpmProblemUserRepository $cpmProblemUserRepo, UserRepositoryEloquent $userRepo, CpmProblemService $cpmProblemService)
    {
        $this->cpmProblemUserRepo = $cpmProblemUserRepo;
        $this->userRepo           = $userRepo;
        $this->cpmProblemService  = $cpmProblemService;
    }

    public function addInstructionToProblem($patientId, $cpmProblemId, $instructionId)
    {
        $cpmProblemUser = $this->repo()->where([
            'patient_id'         => $patientId,
            'cpm_problem_id'     => $cpmProblemId,
            'cpm_instruction_id' => $instructionId,
        ])->first();
        if ( ! $cpmProblemUser) {
            return $this->repo()->create($patientId, $cpmProblemId, $instructionId);
        }
        throw new Exception('a similar instruction->problem relationship already exists');
    }

    public function addProblemToPatient($patientId, $cpmProblemId)
    {
        $problemUser = $this->repo()->create($patientId, $cpmProblemId, null);
        if ($problemUser) {
            return $this->cpmProblemService->setupProblem($problemUser->problems()->first());
        }

        return $problemUser;
    }

    public function getPatientProblems($userId)
    {
        $user = is_a($userId, User::class)
            ? $userId
            : $this->userRepo->model()->findOrFail($userId);

        $user->loadMissing(['cpmProblems']);

        return $user->cpmProblems
            ->map(function ($p) {
                return [
                    'id'          => $p->id,
                    'name'        => $p->name,
                    'code'        => $p->default_icd_10_code,
                    'instruction' => CpmInstruction::find($p->pivot->cpm_instruction_id),
                ];
            });
    }

    public function removeInstructionFromProblem($patientId, $cpmProblemId, $instructionId)
    {
        $this->repo()->where([
            'patient_id'         => $patientId,
            'cpm_problem_id'     => $cpmProblemId,
            'cpm_instruction_id' => $instructionId,
        ])->delete();
    }

    public function removeProblemFromPatient($patientId, $cpmProblemId)
    {
        return $this->repo()->remove($patientId, $cpmProblemId);
    }

    public function repo()
    {
        return $this->cpmProblemUserRepo;
    }
}
