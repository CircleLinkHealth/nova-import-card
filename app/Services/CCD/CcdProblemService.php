<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\CCD;

use App\Models\CCD\Problem as CcdProblem;
use App\Models\CPM\CpmProblem;
use App\Models\ProblemCode;
use App\Repositories\CcdProblemRepository;
use App\Services\CPM\CpmInstructionService;
use CircleLinkHealth\Customer\Entities\User;

class CcdProblemService
{
    private $instructionService;
    private $problemRepo;

    public function __construct(
        CcdProblemRepository $problemRepo,
        CpmInstructionService $instructionService
    ) {
        $this->problemRepo        = $problemRepo;
        $this->instructionService = $instructionService;
    }

    public function addPatientCcdProblem($ccdProblem)
    {
        if ($ccdProblem) {
            if ($ccdProblem['userId'] && $ccdProblem['name'] && strlen($ccdProblem['name']) > 0) {
                $problem = $this->setupProblem($this->problemRepo->addPatientCcdProblem($ccdProblem));

                if ($problem && $ccdProblem['icd10']) {
                    $problemCode                         = new ProblemCode();
                    $problemCode->problem_id             = $problem['id'];
                    $problemCode->problem_code_system_id = 2;
                    $problemCode->code                   = $ccdProblem['icd10'];
                    $problemCode->resolve();
                    $problemCode->save();

                    return $this->problem($problem['id']);
                }

                return $problem;
            }
            throw new \Exception('$ccdProblem needs "userId" and "name" parameters');
        }
        throw new \Exception('$ccdProblem should not be null');
    }

    public function editPatientCcdProblem(
        $userId,
        $ccdProblemId,
        $problemCode = null,
        $is_monitored = null,
        $icd10 = null,
        $instruction = null
    ) {
        $problem = $this->setupProblem($this->problemRepo->editPatientCcdProblem(
            $userId,
            $ccdProblemId,
            $problemCode,
            $is_monitored
        ));

        if ($instruction) {
            $instructionData = null;
            if ($problem['instruction']) {
                $instructionId   = $problem['instruction']->id;
                $instructionData = $this->instructionService->edit($instructionId, $instruction);
            } else {
                $instructionData = $this->instructionService->create($instruction);
            }

            $problem['instruction'] = $instructionData;

            CcdProblem::where([
                'id' => $ccdProblemId,
            ])->update([
                'cpm_instruction_id' => $instructionData->id,
            ]);
        } else {
            CcdProblem::where([
                'id' => $ccdProblemId,
            ])->update([
                'cpm_instruction_id' => null,
            ]);
            $problem['instruction'] = null;
        }

        if ($icd10) {
            $problemCode                         = new ProblemCode();
            $problemCode->problem_id             = $problem['id'];
            $problemCode->problem_code_system_id = 2;
            $problemCode->code                   = $icd10;
            $problemCode->resolve();
            $problemCode->save();

            return $this->problem($problem['id']);
        }

        return $problem;
    }

    public function getPatientProblems($userId)
    {
        $user = is_a($userId, User::class)
            ? $userId
            : User::findOrFail($userId);

        $user->loadMissing(['ccdProblems.cpmInstruction', 'ccdProblems.codes']);

        //exclude generic diabetes type
        $diabetes = \Cache::remember('cpm_problem_diabetes', 1440, function () {
            return CpmProblem::where('name', 'Diabetes')->first();
        });

        return $user->ccdProblems
            ->where('cpm_problem_id', '!=', $diabetes->id)
            ->map([$this, 'setupProblem']);
    }

    public function getPatientProblemsValues($userId)
    {
        return $this->getPatientProblems($userId)
            ->values();
    }

    public function problem($id)
    {
        $problem = CcdProblem::find($id);
        if ($problem) {
            return $this->setupProblem($problem);
        }

        return null;
    }

    public function problems()
    {
        $problems = CcdProblem::groupBy('name')->orderBy('id')->paginate(30);
        $problems->getCollection()->transform(function ($value) {
            return $this->setupProblem($value);
        });

        return $problems;
    }

    public function setupProblem($p)
    {
        if ($p) {
            return [
                'id'            => $p->id,
                'name'          => $p->name,
                'original_name' => $p->original_name,
                'cpm_id'        => $p->cpm_problem_id,
                'codes'         => $p->codes,
                'code'          => $p->icd10code(),
                'is_monitored'  => $p->is_monitored,
                'instruction'   => $p->cpmInstruction,
            ];
        }
    }
}
