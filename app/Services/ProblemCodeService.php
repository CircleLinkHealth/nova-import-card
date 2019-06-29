<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\ProblemCodeSystem;
use App\Repositories\CcdProblemRepository;
use App\Repositories\ProblemCodeRepository;

class ProblemCodeService
{
    private $ccdProblemRepo;
    private $problemCodeRepo;

    public function __construct(
        ProblemCodeRepository $problemCodeRepo,
        CcdProblemRepository $ccdProblemRepo
    ) {
        $this->problemCodeRepo       = $problemCodeRepo;
        $this->ccdProblemRepo        = $ccdProblemRepo;
    }

    public function repo()
    {
        return $this->problemCodeRepo;
    }

    public function system($id)
    {
        return ProblemCodeSystem::findOrFail($id);
    }

    public function systems()
    {
        return ProblemCodeSystem::get();
    }
}
