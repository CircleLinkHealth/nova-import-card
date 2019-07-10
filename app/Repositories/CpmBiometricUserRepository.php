<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use App\Models\CPM\Biometrics\CpmBloodPressure;
use App\Models\CPM\Biometrics\CpmBloodSugar;
use App\Models\CPM\Biometrics\CpmSmoking;
use App\Models\CPM\Biometrics\CpmWeight;
use App\Models\CPM\CpmBiometric;
use App\Models\CPM\CpmBiometricUser;
use CircleLinkHealth\Customer\Entities\User;

class CpmBiometricUserRepository
{
    public function addPatientBiometric($userId, $biometricId)
    {
        if ( ! CpmBiometric::find($biometricId)) {
            throw new \Exception('invalid biometric id "'.$biometricId.'"');
        }
        if ( ! User::find($userId)) {
            throw new \Exception('invalid user id "'.$userId.'"');
        }
        if (CpmBiometricUser::where(['patient_id' => $userId, 'cpm_biometric_id' => $biometricId])->first()) {
            throw new \Exception('mapping between user "'.$userId.'" and biometric "'.$biometricId.'" already exists');
        }
        $biometricUser                   = new CpmBiometricUser();
        $biometricUser->patient_id       = $userId;
        $biometricUser->cpm_biometric_id = $biometricId;
        $biometricUser->save();

        return $this->setupBiometricUser($biometricUser);
    }

    public function addPatientBloodPressure($userId, $biometricId, $biometric)
    {
        if ( ! $this->exists($userId, $biometricId)) {
            $this->addPatientBiometric($userId, $biometricId);
        }
        $biometric['patient_id'] = $userId;
        $pressure                = CpmBloodPressure::firstOrCreate(['patient_id' => $userId]);
        $pressure->save();
        $pressure->update($biometric);

        return $pressure;
    }

    public function addPatientBloodSugar($userId, $biometricId, $biometric)
    {
        if ( ! $this->exists($userId, $biometricId)) {
            $this->addPatientBiometric($userId, $biometricId);
        }
        $biometric['patient_id'] = $userId;
        $pressure                = CpmBloodSugar::firstOrCreate(['patient_id' => $userId]);
        $pressure->save();
        $pressure->update($biometric);

        return $pressure;
    }

    public function addPatientSmoking($userId, $biometricId, $biometric)
    {
        if ( ! $this->exists($userId, $biometricId)) {
            $this->addPatientBiometric($userId, $biometricId);
        }
        $biometric['patient_id'] = $userId;
        $pressure                = CpmSmoking::firstOrCreate(['patient_id' => $userId]);
        $pressure->save();
        $pressure->update($biometric);

        return $pressure;
    }

    public function addPatientWeight($userId, $biometricId, $biometric)
    {
        if ( ! $this->exists($userId, $biometricId)) {
            $this->addPatientBiometric($userId, $biometricId);
        }
        $biometric['patient_id'] = $userId;
        $pressure                = CpmWeight::firstOrCreate(['patient_id' => $userId]);
        $pressure->save();
        $pressure->update($biometric);

        return $pressure;
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

    private function setupBiometricUser($bu)
    {
        $biometric = $bu->biometric()->first();
        switch ($biometric->type) {
            case 0:
                $biometric->info = $bu->weight()->first();
                break;
            case 1:
                $biometric->info = $bu->bloodPressure()->first();
                break;
            case 2:
                $biometric->info = $bu->bloodSugar()->first();
                break;
            case 3:
                $biometric->info = $bu->smoking()->first();
                break;
        }
        $biometric['enabled'] = (bool) $biometric->info;

        return $biometric;
    }
}
