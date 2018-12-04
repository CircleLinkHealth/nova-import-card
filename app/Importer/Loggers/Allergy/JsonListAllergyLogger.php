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

    public function shouldHandle($allergiesString): bool
    {
        $check = is_json($allergiesString);

//        if ($check === false) {
//            throw new \Exception("The string contains invalid json. String: `$allergiesString`");
//        }

        return (boolean)$check;
    }
}
