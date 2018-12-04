<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 12/07/2017
 * Time: 12:32 PM
 */

namespace App\Repositories;

use App\User;
use App\Models\CPM\CpmBiometric;
use App\Models\CPM\CpmBiometricUser;
use App\Models\CPM\Biometrics\CpmBloodPressure;
use App\Models\CPM\Biometrics\CpmBloodSugar;
use App\Models\CPM\Biometrics\CpmSmoking;
use App\Models\CPM\Biometrics\CpmWeight;
use Illuminate\Support\Collection;

class CpmBiometricUserRepository
{
    public function model()
    {
        return app(CpmBiometricUser::class);
    }

    public function biometrics()
    {
        return new Collection([
            $this->pressure(),
            $this->sugar(),
            $this->smoking(),
            $this->weight()
        ]);
    }

    public function pressure()
    {
        return app(CpmBloodPressure::class);
    }
    
    public function sugar()
    {
        return app(CpmBloodSugar::class);
    }
    
    public function smoking()
    {
        return app(CpmSmoking::class);
    }
    
    public function weight()
    {
        return app(CpmWeight::class);
    }

    public function patients($biometricId)
    {
        return $this->model()->where([ 'cpm_biometric_id' => $biometricId ]);
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
        $this->model()->where([
            'patient_id' => $userId,
            'cpm_biometric_id' => $biometricId
        ])->delete();
        return [
            'message' => 'successful'
        ];
    }

    public function setupBiometricUser($bu)
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
        $biometric['enabled'] = !!$biometric->info;
        return $biometric;
    }

    public function patientBiometrics($userId)
    {
        return $this->biometrics()->map(function ($model) use ($userId) {
            $goal = $model->where([ 'patient_id' => $userId ])->first();
            if ($goal) {
                $biometric = $goal->biometric()->first();
                $biometricUser = $this->model()->where([ 'patient_id' => $userId, 'cpm_biometric_id' => $biometric->id ])->first() ?? new CpmBiometricUser();
                $biometric['info'] = $goal;
                $biometric['enabled'] = !!$biometricUser['id'];
                return $biometric;
            } else {
                return null;
            }
        })->filter()->values();
    }

    public function addPatientBiometric($userId, $biometricId)
    {
        if (!CpmBiometric::find($biometricId)) {
            throw new \Exception('invalid biometric id "' . $biometricId . '"');
        } elseif (!User::find($userId)) {
            throw new \Exception('invalid user id "' . $userId . '"');
        } else {
            if ($this->model()->where([ 'patient_id' => $userId, 'cpm_biometric_id' => $biometricId ])->first()) {
                throw new \Exception('mapping between user "' . $userId . '" and biometric "' . $biometricId . '" already exists');
            } else {
                $biometricUser = new CpmBiometricUser();
                $biometricUser->patient_id = $userId;
                $biometricUser->cpm_biometric_id = $biometricId;
                $biometricUser->save();
                return $this->setupBiometricUser($biometricUser);
            }
        }
    }

    public function exists($userId, $biometricId)
    {
        return !!$this->model()->where([
            'patient_id' => $userId,
            'cpm_biometric_id' => $biometricId
        ])->first();
    }

    public function addPatientBloodPressure($userId, $biometricId, $biometric)
    {
        if (!$this->exists($userId, $biometricId)) {
            $this->addPatientBiometric($userId, $biometricId);
        }
        $biometric['patient_id'] = $userId;
        $pressure = $this->pressure()->firstOrCreate(['patient_id' => $userId]);
        $pressure->save();
        $pressure->update($biometric);
        return $pressure;
    }
    
    public function addPatientBloodSugar($userId, $biometricId, $biometric)
    {
        if (!$this->exists($userId, $biometricId)) {
            $this->addPatientBiometric($userId, $biometricId);
        }
        $biometric['patient_id'] = $userId;
        $pressure = $this->sugar()->firstOrCreate(['patient_id' => $userId]);
        $pressure->save();
        $pressure->update($biometric);
        return $pressure;
    }
    
    public function addPatientSmoking($userId, $biometricId, $biometric)
    {
        if (!$this->exists($userId, $biometricId)) {
            $this->addPatientBiometric($userId, $biometricId);
        }
        $biometric['patient_id'] = $userId;
        $pressure = $this->smoking()->firstOrCreate(['patient_id' => $userId]);
        $pressure->save();
        $pressure->update($biometric);
        return $pressure;
    }
    
    public function addPatientWeight($userId, $biometricId, $biometric)
    {
        if (!$this->exists($userId, $biometricId)) {
            $this->addPatientBiometric($userId, $biometricId);
        }
        $biometric['patient_id'] = $userId;
        $pressure = $this->weight()->firstOrCreate(['patient_id' => $userId]);
        $pressure->save();
        $pressure->update($biometric);
        return $pressure;
    }
}
