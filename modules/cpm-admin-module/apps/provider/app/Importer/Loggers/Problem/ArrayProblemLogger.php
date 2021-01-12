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

            if ( ! array_keys_exist(
                [
                    'code',
                    'name',
                    'code_type',
                    'start_date',
                ],
                $p
            ) && array_keys_exist(
                [
                    'code',
                    'code_type',
                ],
                $p
            )) {
                $results[] = Problem::create(
                    [
                        'code'             => $p['code'],
                        'code_system_name' => $p['code_type'],
                    ]
                );

                continue;
            }

            if (1 === count($p) && array_key_exists('name', $p)) {
                $results[] = Problem::create(
                    [
                        'name' => $p['name'],
                    ]
                );

                continue;
            }

            if ( ! empty($p['name']) || ! empty($p['code'])) {
                $results[] = Problem::create(
                    [
                        'code'             => $p['code'] ?? null,
                        'name'             => $p['name'] ?? null,
                        'code_system_name' => $p['code_type'] ?? null,
                        'start'            => $p['start_date'] ?? null,
                    ]
                );
            }
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

            if (array_keys_exist(
                [
                    'code',
                    'name',
                    'code_type',
                    'start_date',
                ],
                $prob
            )) {
                return true;
            }

            if (array_keys_exist(
                [
                    'code',
                    'code_type',
                ],
                $prob
            )) {
                return true;
            }

            if (array_key_exists(
                'name',
                $prob
            )) {
                return true;
            }
        }

        return false;
    }
}
