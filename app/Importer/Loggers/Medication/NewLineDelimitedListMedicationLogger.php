<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 3/12/18
 * Time: 6:27 PM
 */

namespace App\Importer\Loggers\Medication;


use App\Contracts\Importer\MedicalRecord\Section\Logger;

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
                'reference_sig'   => $sig,
            ];
        }

        return $medicationsToImport;
    }

    public function shouldHandle($medicalRecord): bool
    {
        return str_contains($medicalRecord->medications_string,
                "\n") && ! starts_with($medicalRecord->medications_string, ['[', '{']);
    }
}