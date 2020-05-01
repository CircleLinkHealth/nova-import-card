<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Loggers\Problem;

use App\Contracts\Importer\MedicalRecord\Section\Logger;
use CircleLinkHealth\ConditionCodeLookup\Console\Commands\LookupCondition;
use CircleLinkHealth\Eligibility\Entities\Problem;
use Illuminate\Support\Str;

class CommaDelimitedListProblemLogger implements Logger
{
    public function handle($problemsString): array
    {
        $problems = explode(',', $problemsString);

        $results = [];

        foreach ($problems as $problem) {
            $problem = trim($problem);

            if (self::seemsLikeCondtionCode($problem)) {
                $lookup = LookupCondition::lookup($problem, 'any');

                $results[] = Problem::create([
                    'name'             => $lookup['name'] ?? $problem,
                    'code_system_name' => $lookup['type'] ?? null,
                    'code'             => $problem,
                ]);

                continue;
            }

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

    private static function seemsLikeCondtionCode($problem)
    {
        //ICD-10
        if (preg_match('/[A-TV-Z][0-9][0-9AB]\.?[0-9A-TV-Z]{0,4}/', $problem)) {
            return true;
        }

        //ICD-9
        if (preg_match('/\d{3}\.?\d{0,2}/', $problem)) {
            return true;
        }
        if (preg_match('/E\d{3}\.?\d?/', $problem)) {
            return true;
        }
        if (preg_match('/V\d{2}\.?\d{0,2}/', $problem)) {
            return true;
        }

        //SNOMED
        if (is_numeric($problem)) {
            return true;
        }
    }
}
