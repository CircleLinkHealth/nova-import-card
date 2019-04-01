<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\CPM;

use App\Contracts\Services\CpmModel;
use App\Models\CCD\Problem;
use App\Repositories\CpmProblemRepository;
use App\Repositories\UserRepositoryEloquent;
use CircleLinkHealth\Customer\Entities\User;

class CpmProblemService implements CpmModel
{
    private $problemRepo;
    private $userRepo;

    public function __construct(CpmProblemRepository $problemRepo, UserRepositoryEloquent $userRepo)
    {
        $this->problemRepo = $problemRepo;
        $this->userRepo    = $userRepo;
    }

    public function all()
    {
        $problems = $this->repo()->noDiabetesFilter()->get([
            'id',
            'name',
            'default_icd_10_code',
            'is_behavioral',
        ])->map(function ($value) {
            return $this->setupProblem($value);
        });

        return $problems;
    }

    /**
     * @param User $patient
     *
     * @return array|bool
     */
    public function getDetails(User $patient)
    {
        return Problem::where(['patient_id' => $patient->id, 'is_monitored' => 1])->pluck('name');
    }

    public function getProblemsWithInstructionsForUser(User $user)
    {
        $instructions = [];

        //Get all the User's Problems
        $problems = $user->cpmProblems()->get()->sortBy('name')->values()->all();
        if ( ! $problems) {
            return [];
        }

        //For each problem, extract the instructions and
        //store in a key value pair
        foreach ($problems as $problem) {
            if ( ! $problem) {
                continue;
            }

            $instruction = \App\Models\CPM\CpmInstruction::find($problem->pivot->cpm_instruction_id);

            if ($instruction) {
                $instructions[$problem->name] = $instruction->name;
            }
        }

        return $instructions;
    }

    public function problem($id)
    {
        $problem = $this->repo()->model()->find($id);
        if ($problem) {
            return $this->setupProblem($problem);
        }

        return null;
    }

    public function problems()
    {
        $problems = $this->repo()->noDiabetesFilter()->paginate(30);
        $problems->getCollection()->transform(function ($value) {
            return $this->setupProblem($value);
        });

        return $problems;
    }

    public function repo()
    {
        return $this->problemRepo;
    }

    public function setupProblem($p)
    {
        return [
            'id'            => $p->id,
            'name'          => $p->name,
            'code'          => $p->default_icd_10_code,
            'is_behavioral' => $p->is_behavioral,
            'instruction'   => $p->instruction(),
            'snomeds'       => $p->snomedMaps()->where('icd_10_name', '!=', '')->groupBy('icd_10_name')
                ->select(['icd_10_code', 'icd_10_name'])->get(),
        ];
    }

    public function syncWithUser(User $user, array $ids = [], $page = null, array $instructions)
    {
        $user->cpmProblems()->sync($ids);

        $instructionService = app(CpmInstructionService::class);

        foreach ($ids as $problemId) {
            $relationship  = 'cpmProblems';
            $entityId      = $problemId;
            $entityForeign = 'cpm_problem_id';

            if (isset($instructions[$relationship][$entityId])) {
                $instructionInput = $instructions[$relationship][$entityId];

                $instructionService->syncWithUser($user, $relationship, $entityForeign, $entityId, $instructionInput);
            }
        }

        return true;
    }
}
