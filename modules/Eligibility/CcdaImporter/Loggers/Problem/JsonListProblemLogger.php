<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter\Loggers\Problem;

use CircleLinkHealth\Core\Constants\Formats;
use CircleLinkHealth\Eligibility\Contracts\MedicalRecord\Section\Logger;
use CircleLinkHealth\Eligibility\DTO\Problem;

class JsonListProblemLogger implements Logger
{
    public function expects()
    {
        return Formats::JSON;
    }

    public function handle($problemsString): array
    {
//        Expected format
//        {"Problems":[{"Name":"", "CodeType":"" , "Code":"" , "AddedDate":"" , "ResolveDate":"" , "Status":""}]}
        $problems = json_decode($problemsString, true);

        if (is_array($problems) && array_key_exists('Problems', $problems)) {
            return collect($problems['Problems'])
                ->map(function ($problem) {
                    return Problem::create([
                        'name'                   => $problem['Name'],
                        'code'                   => $problem['Code'],
                        'code_system_name'       => $problem['CodeType'],
                        'problem_code_system_id' => getProblemCodeSystemCPMId([$problem['CodeType'] ?? '']),
                        'start'                  => $problem['AddedDate'] ?? null,
                        'end'                    => $problem['ResolveDate'] ?? null,
                        'status'                 => $problem['Status'] ?? null,
                    ]);
                })
                ->filter()
                ->values()
                ->all();
        }

        return [];
    }

    public function shouldHandle($problems)
    {
        return (bool) is_json($problems);
    }
}
