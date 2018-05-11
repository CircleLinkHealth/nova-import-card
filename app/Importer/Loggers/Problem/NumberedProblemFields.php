<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/11/18
 * Time: 2:08 PM
 */

namespace App\Importer\Loggers\Problem;

class NumberedProblemFields
{

    public function handle(&$patient): array
    {
        if ( ! is_array($patient)) {
            return [];
        }

        $problems = [];
        $i        = 1;

        do {
            if ( ! array_key_exists("problem_$i", $patient)) {
                break;
            }

            if ( ! empty($patient["problem_$i"]) && $patient["problem_$i"] != '#N/A') {
                $problems[] = [
                    'Name'        => $patient["problem_$i"],
                    'CodeType'    => '',
                    'Code'        => '',
                    'AddedDate'   => '',
                    'ResolveDate' => '',
                    'Status'      => '',
                ];
            }

            unset($patient["problem_$i"]);

            $i++;
        } while (true);

        return $problems;
    }

    public function shouldHandle($patient)
    {
        if ( ! is_array($patient)) {
            return false;
        }

        return count(preg_grep('/^problem_[\d]*/', array_keys($patient))) > 0;
    }
}