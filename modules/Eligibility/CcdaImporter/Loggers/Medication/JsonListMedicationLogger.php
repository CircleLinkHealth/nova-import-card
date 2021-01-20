<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter\Loggers\Medication;

use App\Contracts\Importer\MedicalRecord\Section\Logger;

class JsonListMedicationLogger implements Logger
{
    public function handle($medicationsString): array
    {
        //Expects format
//        {"Medications":[{"Name":"","Sig":"","StartDate":"","StopDate":"","Status":""}]}
        $medications = json_decode($medicationsString, true);

        if (is_array($medications) && array_key_exists('Medications', $medications)) {
            return collect($medications['Medications'])
                ->map(function ($medication) {
                    return [
                        'reference_title' => trim(str_replace([
                            'Taking',
                            'Continue',
                        ], '', $medication['Name'] ?? '')),
                        'reference_sig' => $medication['Sig'] ?? '',
                    ];
                })
                ->filter()
                ->values()
                ->all();
        }

        return [];
    }

    public function shouldHandle($medications): bool
    {
        $check = is_json($medications);

//        if ($check === false) {
//            throw new \Exception("The string contains invalid json. String: `$medicationsString`");
//        }

        return (bool) $check;
    }
}
