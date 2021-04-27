<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Policies;

use CircleLinkHealth\Customer\Repositories\NurseFinderEloquentRepository;

class CreateNoteForPatient
{
    private NurseFinderEloquentRepository $repo;

    public function __construct(NurseFinderEloquentRepository $repo)
    {
        $this->repo = $repo;
    }

    public static function cacheKey(int $nurseUserId, int $patientId)
    {
        return "nurse_patient_association_{$nurseUserId}_{$patientId}";
    }

    public function can(int $nurseUserId, int $patientId)
    {
        return \Cache::remember(self::cacheKey($nurseUserId, $patientId), 5, function () use ($patientId, $nurseUserId) {
            return optional($this->repo->assignedNurse($patientId))->nurse_user_id === $nurseUserId;
        });
    }
}
