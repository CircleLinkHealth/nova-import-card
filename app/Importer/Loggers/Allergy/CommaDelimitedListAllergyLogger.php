<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Loggers\Allergy;

use App\Contracts\Importer\MedicalRecord\Section\Logger;
use Illuminate\Support\Str;

class CommaDelimitedListAllergyLogger implements Logger
{
    public function handle($allergiesString): array
    {
        $allergies = explode(',', $allergiesString);

        $allergiesToImport = [];

        foreach ($allergies as $allergy) {
            if ('no' == strtolower($allergy)) {
                continue;
            }

            $allergiesToImport[] = $allergy;
        }

        return $allergiesToImport;
    }

    public function shouldHandle($allergies): bool
    {
        return ! Str::startsWith($allergies, ['[', '{']);
    }
}
