<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 3/12/18
 * Time: 6:28 PM
 */

namespace App\Importer\Loggers\Problem;

use App\Contracts\Importer\MedicalRecord\Section\Logger;
use App\Services\Eligibility\Entities\Problem;

class JsonListProblemLogger implements Logger
{
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
                        'start'                  => $problem['AddedDate'],
                        'end'                    => $problem['ResolveDate'],
                        'status'                 => $problem['Status'],
                    ]);
                })
                ->filter()
                ->values()
                ->all();
        }

        return [];
    }

    public function shouldHandle($problemsString)
    {
        $check = is_json($problemsString);

//        if ($check === false) {
//            throw new \Exception("The string contains invalid json. String: `$problemsString`");
//        }

        return (boolean)$check;
    }
}
