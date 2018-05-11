<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/11/18
 * Time: 2:08 PM
 */

namespace App\Importer\Loggers\Allergy;

class NumberedAllergyFields
{

    public function handle(&$patient): array
    {
        if ( ! is_array($patient)) {
            return [];
        }

        $allergies = [];
        $i         = 1;

        do {
            if ( ! array_key_exists("allergy_$i", $patient)) {
                break;
            }

            if ( ! empty($patient["allergy_$i"]) && ! str_contains(strtolower($patient["allergy_$i"]),
                    ['#n/a', 'no known'])) {
                $allergies[] = [
                    'Name'        => $patient["allergy_$i"],
                    'CodeType'    => '',
                    'Code'        => '',
                    'AddedDate'   => '',
                    'ResolveDate' => '',
                    'Status'      => '',
                ];
            }

            unset($patient["allergy_$i"]);

            $i++;
        } while (true);

        return $allergies;
    }

    public function shouldHandle($patient)
    {
        if ( ! is_array($patient)) {
            return false;
        }

        return count(preg_grep('/^allergy_[\d]*/', array_keys($patient))) > 0;
    }
}