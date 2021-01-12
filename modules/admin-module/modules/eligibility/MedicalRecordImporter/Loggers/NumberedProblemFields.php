<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter\Loggers;

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
            if ( ! array_key_exists("problem_${i}", $patient)) {
                break;
            }

            if ( ! empty($patient["problem_${i}"]) && '#N/A' != $patient["problem_${i}"]) {
                $problems[] = [
                    'Name'        => $patient["problem_${i}"],
                    'CodeType'    => '',
                    'Code'        => $patient["problem_${i}"],
                    'AddedDate'   => '',
                    'ResolveDate' => '',
                    'Status'      => '',
                ];
            }

            unset($patient["problem_${i}"]);

            ++$i;
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
