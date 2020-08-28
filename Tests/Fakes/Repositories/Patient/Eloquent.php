<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Patient\Stubs\IsAttachedStub;
use CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Patient\Stubs\IsFulfilledStub;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert as PHPUnit;

class Eloquent implements PatientServiceProcessorRepository
{
    private Collection $collection;
    private Collection $isAttachedStubs;

    private bool $isChargeableServiceEnabledForMonth = false;
    private Collection $isFulfilledStubs;

    public function __construct()
    {
        $this->collection = collect();
    }

    public function assertChargeableSummaryCreated(int $patientId, string $chargeableServiceCode, Carbon $month): void
    {
        PHPUnit::assertTrue(
            1 === $this->collection->where('patientId', $patientId)
                ->where('chargeableServiceCode', $chargeableServiceCode)
                ->where('month', $month)->count()
        );
    }

    public function assertChargeableSummaryNotCreated(int $patientId, string $chargeableServiceCode, Carbon $month): void
    {
        PHPUnit::assertFalse(
            1 === $this->collection->where('patientId', $patientId)
                ->where('chargeableServiceCode', $chargeableServiceCode)
                ->where('month', $month)->count()
        );
    }

    public function fulfill(int $patientId, string $chargeableServiceCode, Carbon $month): ChargeablePatientMonthlySummary
    {
        //TODO: TEST
        $this->collection->push([
            'patientId'             => $patientId,
            'chargeableServiceCode' => $chargeableServiceCode,
            'month'                 => $month,
            'is_fulfilled'          => true,
        ]);

        return new ChargeablePatientMonthlySummary();
    }

    public function getChargeablePatientSummaries(int $patientId, Carbon $month)
    {
        // TODO: Implement getChargeablePatientSummaries() method.
    }

    public function isAttached(int $patientId, string $chargeableServiceCode, Carbon $month): bool
    {
        return (bool) $this->isAttachedStubs
            ->where('chargeableServiceCode', $chargeableServiceCode)
            ->where('month', $month)
            ->where('patientId', $patientId)
            ->pluck('shouldBeAttached')
            ->first();
    }

    public function isChargeableServiceEnabledForLocationForMonth(int $patientId, string $chargeableServiceCode, Carbon $month): bool
    {
        return $this->isChargeableServiceEnabledForMonth;
    }

    public function isFulfilled(int $patientId, string $chargeableServiceCode, Carbon $month): bool
    {
        return (bool) $this->isFulfilledStubs
            ->where('chargeableServiceCode', $chargeableServiceCode)
            ->where('month', $month)
            ->where('patientId', $patientId)
            ->pluck('shouldBeFulfilled')
            ->first();
    }

    /**
     * @param bool $isAttachedStubs
     */
    public function setIsAttachedStubs(IsAttachedStub ...$isAttachedStubs): void
    {
        $this->isAttachedStubs = collect($isAttachedStubs);
    }

    public function setIsChargeableServiceEnabledForMonth(bool $isChargeableServiceEnabledForMonth): void
    {
        $this->isChargeableServiceEnabledForMonth = $isChargeableServiceEnabledForMonth;
    }

    public function setIsFulfilledStubs(IsFulfilledStub ...$isFulfilledStubs): void
    {
        $this->isFulfilledStubs = collect($isFulfilledStubs);
    }

    public function store(int $patientId, string $chargeableServiceCode, Carbon $month): ChargeablePatientMonthlySummary
    {
        $this->collection->push([
            'patientId'             => $patientId,
            'chargeableServiceCode' => $chargeableServiceCode,
            'month'                 => $month,
        ]);

        return new ChargeablePatientMonthlySummary();
    }
}
