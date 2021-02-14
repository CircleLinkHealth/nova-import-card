<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Contracts;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\PatientForcedChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\DTO\ChargeableServiceDuration;
use CircleLinkHealth\SharedModels\Entities\Activity;
use CircleLinkHealth\SharedModels\Entities\PageTimer;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

interface PatientServiceProcessorRepository
{
    public function attachForcedChargeableService(int $patientId, int $chargeableServiceId, Carbon $month = null, string $actionType = PatientForcedChargeableService::FORCE_ACTION_TYPE, ?string $reason = null): void;

    public function createActivityForChargeableService(string $source, PageTimer $pageTimer, ChargeableServiceDuration $chargeableServiceDuration): Activity;

    public function detachForcedChargeableService(int $patientId, int $chargeableServiceId, Carbon $month = null, string $actionType = PatientForcedChargeableService::FORCE_ACTION_TYPE): void;

    public function fulfill(int $patientId, string $chargeableServiceCode, Carbon $month): ChargeablePatientMonthlySummary;

    public function getChargeablePatientSummaries(int $patientId, Carbon $month): EloquentCollection;

    public function getChargeablePatientSummary(int $patientId, string $chargeableServiceCode, Carbon $month): ?ChargeablePatientMonthlySummary;

    public function getChargeablePatientTimesView(int $patientId, Carbon $month): EloquentCollection;

    public function getPatientWithBillingDataForMonth(int $patientId, Carbon $month = null): ?User;

    public function isAttached(int $patientId, string $chargeableServiceCode, Carbon $month): bool;

    public function isChargeableServiceEnabledForLocationForMonth(int $patientId, string $chargeableServiceCode, Carbon $month): bool;

    public function isFulfilled(int $patientId, string $chargeableServiceCode, Carbon $month): bool;

    public function multiAttachServiceSummaries(Collection $processingOutputCollection): void;

    public function requiresPatientConsent(int $patientId, string $chargeableServiceCode, Carbon $month): bool;

    public function setPatientConsented(int $patientId, string $chargeableServiceCode, Carbon $month): ChargeablePatientMonthlySummary;

    public function store(int $patientId, string $chargeableServiceCode, Carbon $month, bool $requiresPatientConsent = false): ChargeablePatientMonthlySummary;
}
