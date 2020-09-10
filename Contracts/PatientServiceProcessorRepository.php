<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Contracts;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;

interface PatientServiceProcessorRepository
{
    public function fulfill(int $patientId, string $chargeableServiceCode, Carbon $month): ChargeablePatientMonthlySummary;

    public function getChargeablePatientSummaries(int $patientId, Carbon $month);

    public function getChargeablePatientSummary(int $patientId, string $chargeableServiceCode, Carbon $month);

    public function isAttached(int $patientId, string $chargeableServiceCode, Carbon $month): bool;

    public function isChargeableServiceEnabledForLocationForMonth(int $patientId, string $chargeableServiceCode, Carbon $month): bool;

    public function isFulfilled(int $patientId, string $chargeableServiceCode, Carbon $month): bool;

    public function store(int $patientId, string $chargeableServiceCode, Carbon $month, bool $requiresPatientConsent = false): ChargeablePatientMonthlySummary;
}
