<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 3/12/18
 * Time: 2:14 PM
 */

namespace App\Importer\Loggers\Allergy;

use App\Contracts\Importer\MedicalRecord\Section\Logger;

class CommaDelimitedListAllergyLogger implements Logger
{
    public function handle($medicalRecord): array
    {
        $allergies = explode(',', $medicalRecord->allergies_string);

        $allergiesToImport = [];

        foreach ($allergies as $allergy) {
            if (strtolower($allergy) == 'no') {
                continue;
            }

            $allergiesToImport[] = $allergy;
        }

        return $allergiesToImport;
    }

    public function shouldHandle($medicalRecord): bool
    {
        return str_contains($medicalRecord->allergies_string, ',') && !starts_with($medicalRecord->allergies_string, ['[', '{']);
    }
}