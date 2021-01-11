<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter\Hooks;

use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportHook;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;

class GetProblemInstruction extends BaseCcdaImportHook
{
    const IMPORTING_LISTENER_NAME = 'import_problem_instructions_default';

    public function run()
    {
        $cpmProblems = \Cache::remember(
            sha1('all_cpm_problems_keyed_by_id'),
            2,
            function () {
                return CpmProblem::get()->keyBy('id');
            }
        );

        $cpmProblem = is_array($this->payload) && array_key_exists('cpm_problem_id', $this->payload)
            ? $cpmProblems[$this->payload['cpm_problem_id']]
            : null;

        return optional($cpmProblem)->instruction();
    }
}
