<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Algorithms\Calls;

use App\Algorithms\Calls\NurseFinder\NurseFinderContract;
use CircleLinkHealth\Customer\AppConfig\StandByNurseUser;
use CircleLinkHealth\Customer\Entities\PatientNurse;
use CircleLinkHealth\Customer\Entities\User;

class NurseFinderRepository implements NurseFinderContract
{
    public function find(int $patientUserId): ?User
    {
        return optional(PatientNurse::with('permanentNurse')
            ->whereHas('permanentNurse')
            ->where('patient_user_id', $patientUserId)
            ->first())->permanentNurse ?? StandByNurseUser::user();
    }
}
