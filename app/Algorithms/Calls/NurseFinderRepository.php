<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Algorithms\Calls;

use App\Algorithms\Calls\NurseFinder\NurseFinderRepositoryContract;
use CircleLinkHealth\Customer\AppConfig\StandByNurseUser;
use CircleLinkHealth\Customer\Entities\PatientNurse;
use CircleLinkHealth\Customer\Entities\User;

class NurseFinderRepository implements NurseFinderRepositoryContract
{
    public function assignedNurse(int $patientUserId): ?PatientNurse
    {
        return PatientNurse::with('permanentNurse')
            ->whereHas('permanentNurse')
            ->where('patient_user_id', $patientUserId)
            ->first();
    }

    public function find(int $patientUserId): ?User
    {
        return optional($this->assignedNurse($patientUserId))->permanentNurse ?? $this->standByNurse();
    }

    public function standByNurse(): ?User
    {
        return StandByNurseUser::user();
    }
}
