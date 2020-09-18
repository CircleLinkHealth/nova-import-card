<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Repositories;

use CircleLinkHealth\Customer\Contracts\NurseFinderRepositoryContract;
use CircleLinkHealth\Customer\Policies\CreateNoteForPatient;
use CircleLinkHealth\Customer\AppConfig\StandByNurseUser;
use CircleLinkHealth\Customer\Entities\PatientNurse;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Facades\Cache;

class NurseFinderEloquentRepository implements NurseFinderRepositoryContract
{
    public function assign(int $patientUserId, int $nurseUserId): bool
    {
        return tap((bool) PatientNurse::updateOrInsert([
            'patient_user_id' => $patientUserId,
        ], [
            'nurse_user_id' => $nurseUserId,
        ]), function ($wasAssigned) use ($patientUserId) {
            if ($wasAssigned) {
                $flushed = Cache::tags(CreateNoteForPatient::cacheTags($patientUserId))->flush();
            }
        });
    }

    public function assignedNurse(int $patientUserId): ?PatientNurse
    {
        return PatientNurse::with('permanentNurse')
            ->whereHas('permanentNurse')
            ->where('patient_user_id', $patientUserId)
            ->first();
    }

    public function deleteAssignment(int $patientUserId)
    {
        return tap(
            (bool) PatientNurse::where('patient_user_id', $patientUserId)->delete(),
            function ($wasDeleted) use ($patientUserId) {
                if ($wasDeleted) {
                    $flushed = Cache::tags(CreateNoteForPatient::cacheTags($patientUserId))->flush();
                }
            }
        );
    }

    public function find(int $patientUserId): ?User
    {
        return optional($this->assignedNurse($patientUserId))->permanentNurse ?? $this->standByNurse();
    }

    public function standByNurse(): ?User
    {
        return app(StandByNurseUser::class)::user();
    }
}
