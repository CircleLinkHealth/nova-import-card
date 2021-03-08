<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Services\CCD;

use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\ApiPatient\ValueObjects\CcdProblemInput;
use CircleLinkHealth\SharedModels\Entities\CpmInstruction;
use CircleLinkHealth\SharedModels\Entities\Problem as CcdProblem;
use CircleLinkHealth\SharedModels\Entities\ProblemCode;
use CircleLinkHealth\SharedModels\Repositories\CcdProblemRepository;
use CircleLinkHealth\SharedModels\Services\CPM\CpmInstructionService;

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

            (app(PatientServiceProcessorRepository::class))->reloadPatientProblems($ccdProblem->getUserId());

            return $problem;
        }
        throw new \Exception('$ccdProblem needs "userId" and "name" parameters');
    }

    public function deletePatientCcdProblem(CcdProblemInput $ccdProblem): bool
    {
        $success = CcdProblem::where([
            'patient_id' => $ccdProblem->getUserId(),
            'id'         => $ccdProblem->getCcdProblemId(),
        ])->delete();

        (app(PatientServiceProcessorRepository::class))->reloadPatientProblems($ccdProblem->getUserId());

        return $success;
    }

    public function editPatientCcdProblem(
        CcdProblemInput $ccdProblem
    ) {
        $problem = $this->setupProblem($this->problemRepo->editPatientCcdProblem(
            $ccdProblem->getUserId(),
            $ccdProblem->getCcdProblemId(),
            $ccdProblem->getCpmProblemId(),
            $ccdProblem->getIsMonitored()
        ));

        if ($instruction = $ccdProblem->getInstruction()) {
            $cpmInstruction = null;
            if ($problem['instruction']) {
                $instructionId  = $problem['instruction']->id;
                $cpmInstruction = $this->editOrCreateNewProblemInstruction($instructionId, $instruction, $ccdProblem->getUserId());
            } else {
                $cpmInstruction = $this->instructionService->create($instruction);
            }

            $problem['instruction'] = $cpmInstruction;

            CcdProblem::where([
                'id' => $ccdProblem->getCcdProblemId(),
            ])->update([
                'cpm_instruction_id' => $cpmInstruction->id,
            ]);
        } else {
            CcdProblem::where([
                'id' => $ccdProblem->getCcdProblemId(),
            ])->update([
                'cpm_instruction_id' => null,
            ]);
            $problem['instruction'] = null;
        }

        if ($ccdProblem->getIcd10()) {
            $problemCode                         = new ProblemCode();
            $problemCode->problem_id             = $problem['id'];
            $problemCode->problem_code_system_id = 2;
            $problemCode->code                   = $ccdProblem->getIcd10();
            $problemCode->resolve();
            $problemCode->save();

            return $this->problem($problem['id']);
        }

        (app(PatientServiceProcessorRepository::class))->reloadPatientProblems($ccdProblem->getUserId());

        return $problem;
    }

    public function getPatientProblems($userId)
    {
        $user = is_a($userId, User::class)
            ? $userId
            : User::findOrFail($userId);

        $user->loadMissing(['ccdProblems.cpmInstruction', 'ccdProblems.codes', 'ccdProblems.cpmProblem']);

        //If patient has been imported via UPG0506 using instructions from the PDF, we need to show ONLY those instructions, avoiding default instructions attached to CPM Problems
        //Check will happen in the front-end
        $shouldShowDefaultInstructions = ! $user->patientIsUPG0506();

        return $user->ccdProblems
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

    private function editOrCreateNewProblemInstruction(int $instructionId, string $instructionText, int $patientId)
    {
        if ($this->instructionService->otherPatientsWithSameInstructionExist($instructionId, $patientId)) {
            return $this->instructionService->create($instructionText);
        }

        $query = CpmInstruction::where('id', $instructionId);
        $query->update(['name' => $instructionText]);

        return $query->first();
    }
}
