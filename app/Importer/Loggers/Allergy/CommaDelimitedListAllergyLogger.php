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
    public function handle($allergiesString): array
    {
        $allergies = explode(',', $allergiesString);

        $allergiesToImport = [];

        foreach ($allergies as $allergy) {
            if (strtolower($allergy) == 'no') {
                continue;
            }

            $allergiesToImport[] = $allergy;
        }

        return $allergiesToImport;
    }

    public function shouldHandle($allergiesString): bool
    {
        return ! starts_with($allergiesString, ['[', '{']);
    }
}
