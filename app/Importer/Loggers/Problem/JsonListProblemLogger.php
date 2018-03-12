<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 3/12/18
 * Time: 6:28 PM
 */

namespace App\Importer\Loggers\Problem;


use App\Contracts\Importer\MedicalRecord\Section\Logger;

class JsonListProblemLogger implements Logger
{

    public function handle($medicalRecord): array
    {
        $problems = json_decode($medicalRecord->problems_string, true);

        if (array_key_exists('Problems', $problems)) {
            return collect($problems['Problems'])
                ->map(function ($problem) {
                    return [
                        'name'                   => $problem['Name'],
                        'code'                   => $problem['Code'],
                        'code_system_name'       => $problem['CodeType'],
                        'problem_code_system_id' => getProblemCodeSystemCPMId([$problem['CodeType'] ?? '']),
                        'start'                  => $problem['AddedDate'],
                        'end'                    => $problem['ResolveDate'],
                        'status'                 => $problem['Status'],
                    ];
                })
                ->filter()
                ->values()
                ->all();
        }

        return [];
    }

    public function shouldHandle($medicalRecord): bool
    {
        return starts_with($medicalRecord->problems_string, ['[', '{']);
    }
}