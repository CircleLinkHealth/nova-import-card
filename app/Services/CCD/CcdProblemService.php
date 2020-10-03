<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\CCD;

use App\Repositories\CcdProblemRepository;
use App\Services\CPM\CpmInstructionService;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Patientapi\ValueObjects\CcdProblemInput;
use CircleLinkHealth\SharedModels\Entities\Problem as CcdProblem;
use CircleLinkHealth\SharedModels\Entities\ProblemCode;

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

    public function addPatientCcdProblem(CcdProblemInput $ccdProblem)
    {
        if ($ccdProblem) {
            if ($ccdProblem->getUserId() && $ccdProblem->getName() && strlen($ccdProblem->getName()) > 0) {
                $problem = $this->setupProblem($this->problemRepo->addPatientCcdProblem($ccdProblem));

                if ($problem && $ccdProblem->getIcd10()) {
                    $problemCode                         = new ProblemCode();
                    $problemCode->problem_id             = $problem['id'];
                    $problemCode->problem_code_system_id = 2;
                    $problemCode->code                   = $ccdProblem->getIcd10();
                    $problemCode->resolve();
                    $problemCode->save();

                    return $this->problem($problem['id']);
                }

                return $problem;
                //save problem and return, processing happens elsewhere
            }
            //perform validation before here
            throw new \Exception('$ccdProblem needs "userId" and "name" parameters');
        }
        //if we pass value object it wouldn't be exception
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

        $user->loadMissing(['ccdProblems.cpmInstruction', 'ccdProblems.codes', 'ccdProblems.cpmProblem']);

        //exclude generic diabetes type
        $diabetes = genericDiabetes();

        //If patient has been imported via UPG0506 using instructions from the PDF, we need to show ONLY those instructions, avoiding default instructions attached to CPM Problems
        //Check will happen in the front-end
        $shouldShowDefaultInstructions = ! $user->patientIsUPG0506();

        return $user->ccdProblems
            ->where('cpm_problem_id', '!=', $diabetes->id)
            ->map(function ($p) use ($shouldShowDefaultInstructions) {
                return $this->setupProblem($p, $shouldShowDefaultInstructions);
            })
            ->filter()
            ->values();
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

    public function setupProblem($p, $shouldShowDefaultInstruction = true)
    {
        if ($p) {
            return [
                'id'                              => $p->id,
                'name'                            => $p->name ?? '',
                'original_name'                   => $p->original_name ?? '',
                'cpm_id'                          => $p->cpm_problem_id,
                'codes'                           => $p->codes,
                'code'                            => $p->icd10code() ?? '',
                'is_monitored'                    => $p->is_monitored,
                'is_behavioral'                   => $p->isBehavioral(),
                'instruction'                     => $p->cpmInstruction,
                'should_show_default_instruction' => $shouldShowDefaultInstruction,
            ];
        }
    }
}
