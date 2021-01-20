<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter\Loggers\Problem;

use CircleLinkHealth\Eligibility\Contracts\MedicalRecord\Section\Logger;
use CircleLinkHealth\Eligibility\Entities\Problem;

class ArrayOfProblemForEligibilityCheck implements Logger
{
    public function handle($problems): array
    {
        return $problems;
    }

    public function shouldHandle($problems)
    {
        if ( ! is_array($problems)) {
            return false;
        }

        $types = collect();

        foreach ($problems as $prob) {
            $types->push(is_a($prob, Problem::class));
        }

        return $types->filter()->isNotEmpty();
    }
}
