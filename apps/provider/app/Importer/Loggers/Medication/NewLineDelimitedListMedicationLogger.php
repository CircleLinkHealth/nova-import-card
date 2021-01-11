<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Loggers\Medication;

use App\Contracts\Importer\MedicalRecord\Section\Logger;
use Illuminate\Support\Str;

class NewLineDelimitedListMedicationLogger implements Logger
{
    public function handle($medicalRecord): array
    {
        $medications = explode("\n", $medicalRecord->medications_string);

        $medications = array_filter($medications);

        $medicationsToImport = [];

        foreach ($medications as $medication) {
            $explodedMed = explode(',', $medication);

            $sig = '';

            if (isset($explodedMed[1])) {
                $sig = trim(str_replace('Sig:', '', $explodedMed[1]));
            }

            $medicationsToImport[] = [
                'reference_title' => trim(str_replace([
                    'Taking',
                    'Continue',
                ], '', $explodedMed[0])),
                'reference_sig' => $sig,
            ];
        }

        return $medicationsToImport;
    }

    public function shouldHandle($medicalRecord): bool
    {
        return Str::contains(
            optional($medicalRecord)->medications_string,
            "\n"
        ) && ! Str::startsWith(optional($medicalRecord)->medications_string, ['[', '{']);
    }
}
