<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\CPM;

use App\Contracts\Services\CpmModel;
use App\Repositories\CpmBiometricRepository;
use App\Repositories\CpmBiometricUserRepository;
use CircleLinkHealth\Customer\Entities\User;

class CpmBiometricService implements CpmModel
{
    private $biometricRepo;
    private $biometricUserRepo;

    public function __construct(CpmBiometricRepository $biometricRepo, CpmBiometricUserRepository $biometricUserRepo)
    {
        $this->biometricRepo     = $biometricRepo;
        $this->biometricUserRepo = $biometricUserRepo;
    }

    public function addPatientBloodPressure($userId, $biometricId, $biometric)
    {
        return $this->biometricUserRepo->addPatientBloodPressure($userId, $biometricId, $biometric);
    }

    public function addPatientBloodSugar($userId, $biometricId, $biometric)
    {
        return $this->biometricUserRepo->addPatientBloodSugar($userId, $biometricId, $biometric);
    }

    public function addPatientSmoking($userId, $biometricId, $biometric)
    {
        return $this->biometricUserRepo->addPatientSmoking($userId, $biometricId, $biometric);
    }

    public function addPatientWeight($userId, $biometricId, $biometric)
    {
        return $this->biometricUserRepo->addPatientWeight($userId, $biometricId, $biometric);
    }

    public function biometric($biometricId)
    {
        $biometric             = $this->biometricUserRepo->model()->find($biometricId);
        $biometric['patients'] = $this->biometricUserRepo->patients($biometricId)->count();

        return $biometric;
    }

    public function biometricPatients($biometricId)
    {
        return $this->biometricUserRepo->patients($biometricId)->get(['patient_id'])->map(function ($u) {
            return $u->patient_id;
        });
    }

    public function biometrics()
    {
        return $this->repo()->biometrics()->map(function ($b) {
            $b['patients'] = $this->biometricUserRepo->patients($b->id)->count();

            return $b;
        });
    }

    public function patientBiometrics($userId)
    {
        return $this->biometricUserRepo->patientBiometrics($userId);
    }

    public function removePatientBiometric($userId, $biometricId)
    {
        return $this->biometricUserRepo->removePatientBiometric($userId, $biometricId);
    }

    public function repo()
    {
        return $this->biometricRepo;
    }

    public function syncWithUser(User $user, array $ids = [], $page = null, array $instructions)
    {
        return $user->cpmBiometrics()->sync($ids);
    }
}
