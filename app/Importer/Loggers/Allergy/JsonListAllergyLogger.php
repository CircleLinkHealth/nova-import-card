<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 3/12/18
 * Time: 5:46 PM
 */

namespace App\Importer\Loggers\Allergy;


use App\Contracts\Importer\MedicalRecord\Section\Logger;

class JsonListAllergyLogger implements Logger
{

    public function handle($medicalRecord): array
    {
        $allergies = json_decode($medicalRecord->allergies_string, true);

        if (array_key_exists('Allergies', $allergies)) {
            return collect($allergies['Allergies'])
                ->map(function ($allergy) {
                    return $allergy['Name'];
                })
                ->filter()
                ->values()
                ->all();
        }

        return [];
    }

    public function shouldHandle($medicalRecord): bool
    {
        return starts_with($medicalRecord->allergies_string, ['[', '{']);
    }
}