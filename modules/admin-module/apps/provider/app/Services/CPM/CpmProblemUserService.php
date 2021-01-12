<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\CPM;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CpmInstruction;
use CircleLinkHealth\SharedModels\Entities\CpmProblemUser;

class CpmProblemUserService
{
    private $cpmProblemService;

    public function __construct(CpmProblemService $cpmProblemService)
    {
        $this->cpmProblemService = $cpmProblemService;
    }

    public function addInstructionToProblem($patientId, $cpmProblemId, $instructionId)
    {
        return $this->create($patientId, $cpmProblemId, $instructionId);
    }

    public function addProblemToPatient($patientId, $cpmProblemId)
    {
        $problemUser = $this->create($patientId, $cpmProblemId, null);
        if ($problemUser) {
            return $this->cpmProblemService->setupProblem(
                \CircleLinkHealth\SharedModels\Entities\CpmProblem::with(['cpmInstructions' => function ($q) {
                    $q->latest();
                }, 'snomedMaps'])->find($cpmProblemId)
            );
        }

        return $problemUser;
    }

    /**
     * @param $patientId
     * @param $cpmProblemId
     * @param null $instructionId
     *
     * @return \CircleLinkHealth\SharedModels\Entities\CpmProblemUser|null
     */
    public function create(int $patientId, int $cpmProblemId, int $instructionId = null)
    {
        return \CircleLinkHealth\SharedModels\Entities\CpmProblemUser::firstOrCreate([
            'patient_id'     => $patientId,
            'cpm_problem_id' => $cpmProblemId,
        ], [
            'cpm_instruction_id' => $instructionId,
        ]);
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
        CpmProblemUser::where([
            'patient_id'         => $patientId,
            'cpm_problem_id'     => $cpmProblemId,
            'cpm_instruction_id' => $instructionId,
        ])->delete();
    }

    public function removeProblemFromPatient($patientId, $cpmProblemId)
    {
        return CpmProblemUser::where(['cpm_problem_id' => $cpmProblemId, 'patient_id' => $patientId])->delete();
    }
}
