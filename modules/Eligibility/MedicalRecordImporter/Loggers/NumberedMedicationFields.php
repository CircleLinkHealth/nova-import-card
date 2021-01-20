<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter\Loggers;

class NumberedMedicationFields
{
    public function handle(&$patient): array
    {
        if ( ! is_array($patient)) {
            return [];
        }

        $medications = [];
        $i           = 1;

        do {
            if ( ! array_key_exists("medication_${i}", $patient)) {
                break;
            }

            if ( ! empty($patient["medication_${i}"]) && '#N/A' != $patient["medication_${i}"]) {
                $decoded = json_decode($patient["medication_${i}"], true);

                if (is_array($decoded) && ! empty($decoded['name'])) {
                    $medications[] = [
                        'Name'      => $decoded['name'],
                        'Sig'       => $decoded['sig'],
                        'StartDate' => $decoded['start_date'],
                        'StopDate'  => $decoded['end_date'],
                        'Status'    => '',
                    ];
                }
            }

            unset($patient["medication_${i}"]);

            ++$i;
        } while (true);

        return $medications;
    }

    public function shouldHandle($patient)
    {
        if ( ! is_array($patient)) {
            return false;
        }

        return count(preg_grep('/^medication_[\d]*/', array_keys($patient))) > 0;
    }
}
