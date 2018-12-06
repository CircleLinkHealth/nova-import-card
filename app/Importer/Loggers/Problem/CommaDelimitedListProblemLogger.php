<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Loggers\Problem;

use App\Contracts\Importer\MedicalRecord\Section\Logger;
use App\Services\Eligibility\Entities\Problem;

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

    public function shouldHandle($problemsString): bool
    {
        return is_string($problemsString) && ! starts_with($problemsString, ['[', '{']);
    }
}
