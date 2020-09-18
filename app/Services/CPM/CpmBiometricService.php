<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\CPM;

use CircleLinkHealth\SharedModels\Contracts\CpmModel;
use App\Repositories\CpmBiometricUserRepository;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CpmBiometric;

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
            $info = $this->isEnabled($biometric, $user);

            return [
                'id'      => $biometric->id,
                'type'    => $biometric->type,
                'name'    => $biometric->name,
                'unit'    => $biometric->unit,
                'info'    => $info,
                'enabled' => (bool) $info,
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

    private function isEnabled(CpmBiometric $biometric, User $user)
    {
        switch ($biometric->name) {
            case \CircleLinkHealth\SharedModels\Entities\CpmBiometric::BLOOD_PRESSURE:
                return $user->cpmBloodPressure;
                break;
            case \CircleLinkHealth\SharedModels\Entities\CpmBiometric::BLOOD_SUGAR:
                return $user->cpmBloodSugar;
                break;
            case \CircleLinkHealth\SharedModels\Entities\CpmBiometric::WEIGHT:
                return $user->cpmWeight;
                break;
            case \CircleLinkHealth\SharedModels\Entities\CpmBiometric::SMOKING:
                return $user->cpmSmoking;
                break;
        }
    }
}
