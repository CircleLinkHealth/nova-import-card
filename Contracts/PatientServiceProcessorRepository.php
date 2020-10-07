<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Contracts;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummaryView;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Collection;

interface PatientServiceProcessorRepository
{
    public function fulfill(int $patientId, string $chargeableServiceCode, Carbon $month): ChargeablePatientMonthlySummary;

    public function getChargeablePatientSummaries(int $patientId, Carbon $month): Collection;

    public function getChargeablePatientSummary(int $patientId, string $chargeableServiceCode, Carbon $month): ?ChargeablePatientMonthlySummaryView;

    public function getPatientWithBillingDataForMonth(int $patientId, Carbon $month = null): ?User;

    public function isAttached(int $patientId, string $chargeableServiceCode, Carbon $month): bool;

    public function isChargeableServiceEnabledForLocationForMonth(int $patientId, string $chargeableServiceCode, Carbon $month): bool;

    public function isFulfilled(int $patientId, string $chargeableServiceCode, Carbon $month): bool;

    public function setPatientConsented(int $patientId, string $chargeableServiceCode, Carbon $month): ChargeablePatientMonthlySummary;

    public function store(int $patientId, string $chargeableServiceCode, Carbon $month, bool $requiresPatientConsent = false): ChargeablePatientMonthlySummary;
}
