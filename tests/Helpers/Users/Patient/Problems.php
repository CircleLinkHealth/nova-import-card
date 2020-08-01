<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Helpers\Users\Patient;

use CircleLinkHealth\Eligibility\Entities\PcmProblem;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;

trait Problems
{
    public function attachValidPcmProblem(\CircleLinkHealth\Customer\Entities\User $patient)
    {
        $problem = $patient->ccdProblems()->create([
            'name'           => 'Test Valid PCM Problem',
            'cpm_problem_id' => CpmProblem::whereNotNull('default_icd_10_code')->firstOrFail()->id,
        ]);

        $pcmProblem = PcmProblem::create([
            'practice_id' => $patient->program_id,
            'code_type'   => 'ICD-10',
            'code'        => $problem->icd10Code(),
            'description' => $problem->name,
        ]);

        return $problem;
    }
}
