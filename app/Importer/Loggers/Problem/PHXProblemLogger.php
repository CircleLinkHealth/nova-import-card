<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Loggers\Problem;

use App\Contracts\Importer\MedicalRecord\Section\Logger;
use CircleLinkHealth\Eligibility\Entities\Problem;

class PHXProblemLogger implements Logger
{
    public function handle($problems): array
    {
        foreach ($problems as $p) {
            $results[] = Problem::create([
                'end'                    => $p['end'],
                'code'                   => $p['code'],
                'name'                   => $p['name'],
                'start'                  => $p['start'],
                'status'                 => $p['status'],
                'code_system_name'       => $p['code_system_name'],
                'problem_code_system_id' => $p['problem_code_system_id'],
            ]);
        }

        return $results;
    }

    public function shouldHandle($problems)
    {
        if ( ! is_array($problems)) {
            return false;
        }

        foreach ($problems as $prob) {
            if ( ! array_keys_exist([
                'end',
                'code',
                'name',
                'start',
                'status',
                'code_system_name',
                'problem_code_system_id',
            ], $prob)) {
                return false;
            }
        }

        return true;
    }
}
