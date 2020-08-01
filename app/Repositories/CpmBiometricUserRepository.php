<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use CircleLinkHealth\SharedModels\Entities\CpmBiometricUser;
use CircleLinkHealth\SharedModels\Entities\CpmBloodPressure;
use CircleLinkHealth\SharedModels\Entities\CpmBloodSugar;
use CircleLinkHealth\SharedModels\Entities\CpmSmoking;
use CircleLinkHealth\SharedModels\Entities\CpmWeight;

class CpmBiometricUserRepository
{
    public function addPatientBiometric($userId, $biometricId)
    {
        return CpmBiometricUser::create([
            'patient_id'       => $userId,
            'cpm_biometric_id' => $biometricId,
        ]);
    }

    public function addPatientBloodPressure($userId, $biometricId, $biometric)
    {
        if ( ! $this->exists($userId, $biometricId)) {
            $this->addPatientBiometric($userId, $biometricId);
        }

        return CpmBloodPressure::updateOrCreate(
            ['patient_id' => $userId],
            $biometric
        );
    }

    public function addPatientBloodSugar($userId, $biometricId, $biometric)
    {
        if ( ! $this->exists($userId, $biometricId)) {
            $this->addPatientBiometric($userId, $biometricId);
        }

        return CpmBloodSugar::updateOrCreate(
            ['patient_id' => $userId],
            $biometric
        );
    }

    public function addPatientSmoking($userId, $biometricId, $biometric)
    {
        if ( ! $this->exists($userId, $biometricId)) {
            $this->addPatientBiometric($userId, $biometricId);
        }

        return CpmSmoking::updateOrCreate(
            ['patient_id' => $userId],
            $biometric
        );
    }

    public function addPatientWeight($userId, $biometricId, $biometric)
    {
        if ( ! $this->exists($userId, $biometricId)) {
            $this->addPatientBiometric($userId, $biometricId);
        }

        return CpmWeight::updateOrCreate(
            ['patient_id' => $userId],
            $biometric
        );
    }

    public function exists($userId, $biometricId)
    {
        return (bool) CpmBiometricUser::where([
            'patient_id'       => $userId,
            'cpm_biometric_id' => $biometricId,
        ])->exists();
    }

    public function patients($biometricId)
    {
        return CpmBiometricUser::where(['cpm_biometric_id' => $biometricId]);
    }

    public function removePatientBiometric($userId, $biometricId)
    {
        // switch ($biometricId) {
        //     case 1:
        //         $this->weight()->where([ 'patient_id' => $userId ])->delete();
        //         break;
        //     case 2:
        //         $this->pressure()->where([ 'patient_id' => $userId ])->delete();
        //         break;
        //     case 3:
        //         $this->sugar()->where([ 'patient_id' => $userId ])->delete();
        //         break;
        //     case 4:
        //         $this->smoking()->where([ 'patient_id' => $userId ])->delete();
        //         break;
        // }
        CpmBiometricUser::where([
            'patient_id'       => $userId,
            'cpm_biometric_id' => $biometricId,
        ])->delete();

        return [
            'message' => 'successful',
        ];
    }
}
