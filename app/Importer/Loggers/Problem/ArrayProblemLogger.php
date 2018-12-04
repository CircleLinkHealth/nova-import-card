<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 9/11/18
 * Time: 10:35 PM
 */

namespace App\Importer\Loggers\Problem;

use App\Contracts\Importer\MedicalRecord\Section\Logger;
use App\Services\Eligibility\Entities\Problem;

class ArrayProblemLogger implements Logger
{
    public function handle($problems): array
    {
        foreach ($problems as $p) {
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
        if (! is_array($problems)) {
            return false;
        }

        foreach ($problems as $prob) {
            if (! array_keys_exist([
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
