<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Caches;

use CircleLinkHealth\Customer\Entities\User;

class BillingDataCache implements BillingCache
{
    protected array $locationServiceCache = [];
    protected array $patientCache         = [];

    protected array $queriedLocationServices = [];

    protected array $queriedPatients = [];

    public function clearPatients(): void
    {
        $this->patientCache    = [];
        $this->queriedPatients = [];
    }

    public function forgetPatient(int $patientId): void
    {
        $this->patientCache    = collect($this->patientCache)->filter(fn ($p) => $p->id != $patientId)->toArray();
        $this->queriedPatients = collect($this->patientCache)->filter(fn ($id) => $id != $patientId)->toArray();
    }

    public function getPatient(int $patientId): User
    {
        return collect($this->patientCache)->firstWhere('id', $patientId);
    }

    public function patientWasQueried(int $patientId): bool
    {
        return in_array($patientId, $this->queriedPatients);
    }
    
    public function patientExistsInCache(int $patientId): bool
    {
        return collect($this->patientCache)->where('id', $patientId)->isNotEmpty();
    }

    public function setPatientInCache(User $patientUser): void
    {
        $this->patientCache[] = $patientUser;
    }

    public function setQueriedPatient(int $patientId): void
    {
        $this->queriedPatients[] = $patientId;
    }
}
