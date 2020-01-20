<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Loggers\Problem;

use App\Contracts\Importer\MedicalRecord\Section\Logger;
use CircleLinkHealth\Eligibility\Entities\Problem;

class ArrayProblemLogger implements Logger
{
    public function handle($problems): array
    {
        $results = [];

        foreach ($problems as $p) {
            if ( ! is_array($p)) {
                continue;
            }
            $results[] = Problem::create([
                'code'             => $p['code'],
                'name'             => $p['name'],
                'code_system_name' => $p['code_type'],
                'start'            => $p['start_date'],
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
            if ( ! is_array($prob)) {
                \Log::error('NOT AN ARRAY:'.json_encode($problems));

                return false;
            }

            if ( ! array_keys_exist([
                'code',
                'name',
                'code_type',
                'start_date',
            ], $prob)) {
                return false;
            }
        }

        return true;
    }
}
