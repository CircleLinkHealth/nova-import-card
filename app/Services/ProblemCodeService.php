<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Models\ProblemCode;
use App\Repositories\CcdProblemRepository;
use App\Repositories\ProblemCodeRepository;
use App\Repositories\ProblemCodeSystemRepository;

class ProblemCodeService
{
    private $ccdProblemRepo;
    private $problemCodeRepo;

    public function __construct(
        ProblemCodeRepository $problemCodeRepo,
                                ProblemCodeSystemRepository $problemCodeSystemRepo,
                                CcdProblemRepository $ccdProblemRepo
    ) {
        $this->problemCodeRepo       = $problemCodeRepo;
        $this->problemCodeSystemRepo = $problemCodeSystemRepo;
        $this->ccdProblemRepo        = $ccdProblemRepo;
    }

    public function add(ProblemCode $problemCode)
    {
        if ($problemCode) {
            $problem = $this->ccdProblemRepo->problem($problemCode->problem_id);
            $system  = $problemCode->system()->first();
            if ($system) {
                if (!$this->repo()->exists($problemCode->problem_id, $problemCode->problem_code_system_id)) {
                    $problemCode->code_system_name = $system->name;
                    $problemCode->resolve();
                    $problemCode->save();

                    return $problemCode;
                }

                return $this->repo()->model()->where([
                    'problem_code_system_id' => $problemCode->problem_code_system_id,
                    'problem_id'             => $problemCode->problem_id,
                ])->first();
            }
            throw new Exception('Invalid problem_code_system_id value');
        }
        throw new Exception('$problemCode must exist');
    }

    public function repo()
    {
        return $this->problemCodeRepo;
    }

    public function system($id)
    {
        return $this->problemCodeSystemRepo->model()->findOrFail($id);
    }

    public function systems()
    {
        return $this->problemCodeSystemRepo->model()->get();
    }
}
