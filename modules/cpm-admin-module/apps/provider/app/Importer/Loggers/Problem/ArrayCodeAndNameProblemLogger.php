<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Loggers\Problem;

use App\Contracts\Importer\MedicalRecord\Section\Logger;
use CircleLinkHealth\Eligibility\Entities\Problem;

class ArrayCodeAndNameProblemLogger implements Logger
{
    public function handle($problems): array
    {
        foreach ($problems as $p) {
            $results[] = Problem::create([
                'code' => $p['Code'],
                'name' => $p['Name'],
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
                'Code',
                'Name',
            ], $prob)) {
                return false;
            }
        }

        return true;
    }
}
