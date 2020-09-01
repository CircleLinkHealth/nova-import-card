<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Policies;

use App\Algorithms\Calls\NurseFinder\NurseFinderEloquentRepository;

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

    public static function cacheTags(int $patientId)
    {
        return [
            "nurse_patient_associations_for_patient_$patientId"
        ];
    }

    public function can(int $nurseUserId, int $patientId)
    {
        return \Cache::tags(self::cacheTags($patientId))->remember(self::cacheKey($nurseUserId, $patientId), 2, function () use ($patientId, $nurseUserId) {
            return optional($this->repo->assignedNurse($patientId))->nurse_user_id === $nurseUserId;
        });
    }
}
