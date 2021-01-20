<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter\Loggers\Allergy;

use CircleLinkHealth\Eligibility\Contracts\MedicalRecord\Section\Logger;

class JsonListAllergyLogger implements Logger
{
    public function handle($allergiesString): array
    {
//        Format
//        {"Allergies":[{"Name":""}, {"Name":""}, {"Name":""}]}
        $allergies = json_decode($allergiesString, true);

        if (is_array($allergies) && array_key_exists('Allergies', $allergies)) {
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

    public function shouldHandle($allergies): bool
    {
        $check = is_json($allergies);

//        if ($check === false) {
//            throw new \Exception("The string contains invalid json. String: `$allergiesString`");
//        }

        return (bool) $check;
    }
}
