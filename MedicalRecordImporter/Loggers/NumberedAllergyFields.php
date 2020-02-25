<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter\Loggers;

class NumberedAllergyFields
{
    public function handle(&$patient): array
    {
        if ( ! is_array($patient)) {
            return [];
        }

        $allergies = [];
        $i         = 1;

        do {
            if ( ! array_key_exists("allergy_${i}", $patient)) {
                break;
            }

            if ( ! empty($patient["allergy_${i}"]) && ! Illuminate\Support\Str::contains(
                strtolower($patient["allergy_${i}"]),
                ['#n/a', 'no known']
            )) {
                $allergies[] = [
                    'Name' => $patient["allergy_${i}"],
                ];
            }

            unset($patient["allergy_${i}"]);

            ++$i;
        } while (true);

        return $allergies;
    }

    public function shouldHandle($patient)
    {
        if ( ! is_array($patient)) {
            return false;
        }

        return count(preg_grep('/^allergy_[\d]*/', array_keys($patient))) > 0;
    }
}
