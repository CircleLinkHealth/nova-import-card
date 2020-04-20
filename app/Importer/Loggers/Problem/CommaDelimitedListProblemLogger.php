<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Loggers\Problem;

use App\Contracts\Importer\MedicalRecord\Section\Logger;
use CircleLinkHealth\Eligibility\Entities\Problem;
use Illuminate\Support\Str;

class CommaDelimitedListProblemLogger implements Logger
{
    public function handle($problemsString): array
    {
        $problems = explode(',', $problemsString);

        $results = [];

        foreach ($problems as $problem) {
            $results[] = Problem::create([
                'name' => $problem,
                'code' => $problem,
            ]);
        }

        return $results;
    }

    public function shouldHandle($problems): bool
    {
        return is_string($problems) && ! Str::startsWith($problems, ['[', '{']);
    }
}
