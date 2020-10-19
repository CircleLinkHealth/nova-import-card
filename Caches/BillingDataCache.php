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

    public function getPatient(int $patientId): User
    {
        return collect($this->patientCache)->firstWhere('id', $patientId);
    }

    public function patientWasQueried(int $patientId): bool
    {
        return in_array($patientId, $this->queriedPatients);
    }

    public function setPatientInCache(User $patientUser): void
    {
        $this->patientCache[] = $patientUser;
    }

    //todo: abstract methods to dynamic entities?
    public function setQueriedPatient(int $patientId): void
    {
        $this->queriedPatients[] = $patientId;
    }
}
