<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 3/12/18
 * Time: 6:26 PM
 */

namespace App\Importer\Loggers\Medication;


use App\Contracts\Importer\MedicalRecord\Section\Logger;

class JsonListMedicationLogger implements Logger
{

    public function handle($medicalRecord): array
    {
        $medications = json_decode($medicalRecord->medications_string, true);

        if (array_key_exists('Medications', $medications)) {
            return collect($medications['Medications'])
                ->map(function ($medication) {
                    return [
                        'reference_title' => trim(str_replace([
                            'Taking',
                            'Continue',
                        ], '', $medication['Name'] ?? '')),
                        'reference_sig'   => $medication['Sig'] ?? '',
                    ];
                })
                ->filter()
                ->values()
                ->all();
        }

        return [];
    }

    public function shouldHandle($medicalRecord): bool
    {
        return starts_with($medicalRecord->medications_string, ['[', '{']);
    }
}