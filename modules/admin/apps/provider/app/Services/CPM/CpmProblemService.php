<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\CPM;

use App\Contracts\Services\CpmModel;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Problem;
use Illuminate\Support\Facades\Cache;

class CpmProblemService implements CpmModel
{
    public function all()
    {
        return Cache::store('array')->rememberForever('CpmProblemService_all', function () {
            return $this->noDiabetesFilter()->withLatestCpmInstruction()->withIcd10Codes()->get([
                'id',
                'name',
                'default_icd_10_code',
                'is_behavioral',
            ])->map(function ($value) {
                return $this->setupProblem($value);
            });
        });
    }

    /**
     * @return array|bool
     */
    public function getDetails(User $patient)
    {
        return Problem::where(['patient_id' => $patient->id, 'is_monitored' => 1])->pluck('name');
    }

    public function getProblemsWithInstructionsForUser(User $user)
    {
        $user->loadMissing('ccdProblems.cpmInstruction', 'ccdProblems.cpmProblem');

        $instructions = $user->ccdProblems->where('cpm_problem_id', '!=', null)->unique('cpm_problem_id')->sortBy('cpmProblem.name')->mapWithKeys(function ($problem) {
            return [optional($problem->cpmProblem)->name => optional($problem->cpmInstruction)->name];
        });

        return $instructions->all();
    }

    public function noDiabetesFilter()
    {
        return \CircleLinkHealth\SharedModels\Entities\CpmProblem::where('name', '!=', 'Diabetes');
    }

    public function problem($id)
    {
        $problem = \CircleLinkHealth\SharedModels\Entities\CpmProblem::withLatestCpmInstruction()->withIcd10Codes()->find($id);
        if ($problem) {
            return $this->setupProblem($problem);
        }

        return null;
    }

    public function problems()
    {
        $problems = $this->noDiabetesFilter()->withLatestCpmInstruction()->withIcd10Codes()->paginate(30);
        $problems->getCollection()->transform(function ($value) {
            return $this->setupProblem($value);
        });

        return $problems;
    }

    public function setupProblem($p)
    {
        return [
            'id'            => $p->id,
            'name'          => $p->name,
            'code'          => $p->default_icd_10_code,
            'is_behavioral' => $p->is_behavioral,
            'instruction'   => optional($p->cpmInstructions)->first(),
            'snomeds'       => $p->snomedMaps->transform(function ($snomed) {
                return ['icd_10_code' => $snomed->icd_10_code, 'icd_10_name' => $snomed->icd_10_name];
            }),
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
