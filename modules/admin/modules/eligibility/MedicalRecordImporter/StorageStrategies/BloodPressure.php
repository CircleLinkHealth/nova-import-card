<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter\StorageStrategies;

use CircleLinkHealth\Eligibility\MedicalRecordImporter\StorageStrategy;
use CircleLinkHealth\SharedModels\Entities\CpmBiometric;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/25/16
 * Time: 11:55 PM.
 */
class BloodPressure extends BaseStorageStrategy implements StorageStrategy
{
    public function import($bloodPressure)
    {
        $this->user->cpmBloodPressure()->create([
            'starting' => $bloodPressure,
        ]);

        $biometric = CpmBiometric::whereName(CpmBiometric::BLOOD_PRESSURE)->first();

        try {
            if ( ! $this->user->cpmBiometrics()->where('id', $biometric->id)->exists()) {
                $this->user->cpmBiometrics()->attach($biometric->id);
            }
        } catch (\Exception $e) {
            //check if this is a mysql exception for unique key constraint
            if ($e instanceof \Illuminate\Database\QueryException) {
                //                    @todo:heroku query to see if it exists, then attach
                $errorCode = $e->errorInfo[1];
                if (1062 == $errorCode) {
                    //do nothing
                    //we don't actually want to terminate the program if we detect duplicates
                    //we just don't wanna add the row again
                }
            }
        }
    }

    public function parse($jsonDecodedCcda)
    {
        $codesSystolic = [
            '8480-6',
        ];

        $namesSystolic = [
            'systolic blood pressure',
        ];

        $codesDiastolic = [
            '8462-4',
        ];

        $namesDiastolic = [
            'diastolic blood pressure',
        ];

        $vitals = $jsonDecodedCcda->vitals;

        if (empty($vitals)) {
            return;
        }

        foreach ($vitals as $vital) {
            foreach ($vital->results as $result) {
                if (in_array($result->code, $codesSystolic) || in_array(strtolower($result->name), $namesSystolic)) {
                    $systolic = $result->value;
                }

                if (in_array($result->code, $codesDiastolic) || in_array(strtolower($result->name), $namesDiastolic)) {
                    $diastolic = $result->value;
                }

                if ( ! empty($systolic) && ! empty($diastolic)) {
                    return "{$systolic}/{$diastolic}";
                }
            }
        }

        return false;
    }
}
