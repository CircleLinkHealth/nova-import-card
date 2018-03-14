<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 3/12/18
 * Time: 6:27 PM
 */

namespace App\Importer\Loggers\Problem;


use App\Contracts\Importer\MedicalRecord\Section\Logger;

class CommaDelimitedListProblemLogger implements Logger
{

    public function handle($medicalRecord): array
    {
        $problems = explode(',', $medicalRecord->problems_string);

        foreach ($problems as $problem) {
//            @todo: implement once a use case comes up
//            $problem = trim($problem);
//
//            if (ctype_alpha(str_replace([
//                "\n",
//                "\t",
//                ' ',
//            ], '', $problem))) {
//                $problem = [
//                    'name' => $problem,
//                ];
//            }
//
//            $problem = [
//                'code' => $problem,
//            ];
        }
    }

    public function shouldHandle($medicalRecord): bool
    {
        return str_contains($medicalRecord->problems_string, ',') && !starts_with($medicalRecord->problems_string, ['[', '{']);
    }
}