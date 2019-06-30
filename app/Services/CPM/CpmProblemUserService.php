<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\CPM;

use App\Models\CPM\CpmInstruction;
use App\Repositories\CpmProblemUserRepository;
use CircleLinkHealth\Customer\Entities\User;

class CpmProblemUserService
{
    private $cpmProblemService;
    private $cpmProblemUserRepo;

    public function __construct(CpmProblemUserRepository $cpmProblemUserRepo, CpmProblemService $cpmProblemService)
    {
        $this->cpmProblemUserRepo = $cpmProblemUserRepo;
        $this->cpmProblemService  = $cpmProblemService;
    }

    public function addInstructionToProblem($patientId, $cpmProblemId, $instructionId)
    {
        $cpmProblemUser = $this->cpmProblemUserRepo->where([
            'patient_id'         => $patientId,
            'cpm_problem_id'     => $cpmProblemId,
            'cpm_instruction_id' => $instructionId,
        ])->first();
        if ( ! $cpmProblemUser) {
            return $this->cpmProblemUserRepo->create($patientId, $cpmProblemId, $instructionId);
        }
        throw new \Exception('a similar instruction->problem relationship already exists');
    }

    public function addProblemToPatient($patientId, $cpmProblemId)
    {
        $problemUser = $this->cpmProblemUserRepo->create($patientId, $cpmProblemId, null);
        if ($problemUser) {
            return $this->cpmProblemService->setupProblem($problemUser->problems()->first());
        }

        return $problemUser;
    }

    public function getPatientProblems($userId)
    {
        $user = is_a($userId, User::class)
            ? $userId
            : User::findOrFail($userId);

        $user->loadMissing(['cpmProblems']);

        $instructions = CpmInstruction::findMany($user->cpmProblems->pluck('pivot.cpm_instruction_id')->all());

        return $user->cpmProblems
            ->map(function ($p) use ($instructions) {
                return [
                    'id'          => $p->id,
                    'name'        => $p->name,
                    'code'        => $p->default_icd_10_code,
                    'instruction' => $instructions->firstWhere('id', $p->pivot->cpm_instruction_id),
                ];
            });
    }

    public function removeInstructionFromProblem($patientId, $cpmProblemId, $instructionId)
    {
        $this->cpmProblemUserRepo->where([
            'patient_id'         => $patientId,
            'cpm_problem_id'     => $cpmProblemId,
            'cpm_instruction_id' => $instructionId,
        ])->delete();
    }

    public function removeProblemFromPatient($patientId, $cpmProblemId)
    {
        return $this->cpmProblemUserRepo->remove($patientId, $cpmProblemId);
    }
}
