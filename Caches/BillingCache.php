<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Caches;

use CircleLinkHealth\Customer\Entities\User;

interface BillingCache
{
    public function billingRevampIsEnabled(): bool;
    
    public function setBillingRevampIsEnabled(bool $isEnabled): void;
    
    public function clearPatients(array $patientIds = []): void;

    public function forgetPatient(int $patientId): void;

    public function getPatient(int $patientId): ?User;

    public function patientExistsInCache(int $patientId): bool;

    public function patientWasQueried(int $patientId): bool;

    public function setPatientInCache(User $patientUser): void;

    public function setQueriedPatient(int $patientId): void;
}
