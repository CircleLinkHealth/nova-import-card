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

    public function can(int $nurseUserId, int $patientId)
    {
        return \Cache::remember("nurse_patient_association_{$nurseUserId}_$patientId", 2, function () use ($patientId, $nurseUserId) {
            return optional($this->repo->find($patientId))->id === $nurseUserId;
        });
    }
}
