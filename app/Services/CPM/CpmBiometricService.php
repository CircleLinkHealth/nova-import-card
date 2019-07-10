<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\CPM;

use App\Contracts\Services\CpmModel;
use App\Models\CPM\CpmBiometricUser;
use App\Repositories\CpmBiometricUserRepository;
use CircleLinkHealth\Customer\Entities\User;

class CpmBiometricService implements CpmModel
{
    private $biometricUserRepo;

    public function __construct(CpmBiometricUserRepository $biometricUserRepo)
    {
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
        $biometric             = CpmBiometricUser::find($biometricId);
        $biometric['patients'] = $this->biometricUserRepo->patients($biometricId)->count();

        return $biometric;
    }

    public function biometricPatients($biometricId)
    {
        return $this->biometricUserRepo->patients($biometricId)->get(['patient_id'])->map(function ($u) {
            return $u->patient_id;
        });
    }

    public function patientBiometrics($userId)
    {
        if (is_a($userId, User::class)) {
            $user = $userId;

            $user->loadMissing([
                'cpmBiometrics',
            ]);
        } else {
            $user = User::with('cpmBiometrics')->findOrFail($userId);
        }

        return $user->cpmBiometrics->map(function ($biometric) use ($user) {
            return [
                'info'    => $biometric,
                'enabled' => (bool) $biometric->pivot->patient_id,
            ];
        });
    }

    public function removePatientBiometric($userId, $biometricId)
    {
        return $this->biometricUserRepo->removePatientBiometric($userId, $biometricId);
    }

    public function syncWithUser(User $user, array $ids = [], $page = null, array $instructions)
    {
        return $user->cpmBiometrics()->sync($ids);
    }
}
