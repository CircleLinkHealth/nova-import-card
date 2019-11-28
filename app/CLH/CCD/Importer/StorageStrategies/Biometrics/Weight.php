<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\CLH\CCD\Importer\StorageStrategies\Biometrics;

use App\CLH\CCD\Importer\StorageStrategies\BaseStorageStrategy;
use App\CLH\Contracts\CCD\StorageStrategy;
use App\Models\CPM\CpmBiometric;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/25/16
 * Time: 11:55 PM.
 */
class Weight extends BaseStorageStrategy implements StorageStrategy
{
    public function import($weight)
    {
        $this->user->cpmWeight()->create([
            'starting' => $weight,
        ]);

        $biometric = CpmBiometric::whereName(CpmBiometric::WEIGHT)->first();

        try {
            $this->user->cpmBiometrics()->attach($biometric->id);
        } catch (\Exception $e) {
            //check if this is a mysql exception for unique key constraint
            if ($e instanceof \Illuminate\Database\QueryException) {
                //                    @todo:heroku query to see if it exists, then attach

                $errorCode = $e->errorInfo[0];
                if (23505 == $errorCode) {
                    //do nothing
                    //we don't actually want to terminate the program if we detect duplicates
                    //we just don't wanna add the row again
                }
            }
        }
    }

    public function parse($jsonDecodedCcda)
    {
        $codes = [
            '3141-9', //body weight, measured
        ];

        $names = [
            'weight',
            'body weight',
        ];

        $unit = [
            'kg',
            'kilos',
            'kilograms',
            'kgs',
        ];

        $vitals = $jsonDecodedCcda->vitals;

        if (empty($vitals)) {
            return;
        }

        foreach ($vitals as $vital) {
            foreach ($vital->results as $result) {
                if (in_array($result->code, $codes) || in_array(strtolower($result->name), $names)) {
                    $weight = $result->value;

                    if (in_array($result->unit, $unit)) {
                        //if it's in kg, we convert to lbs
                        $weight = round($weight * 2.2046, 1);
                    }

                    return $weight;
                }
            }
        }

        return false;
    }
}
