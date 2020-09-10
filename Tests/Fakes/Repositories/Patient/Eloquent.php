<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Patient\Stubs\ChargeablePatientMonthlySummaryStub;
use CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Patient\Stubs\IsAttachedStub;
use CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Patient\Stubs\IsFulfilledStub;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert as PHPUnit;

class Eloquent implements PatientServiceProcessorRepository
{
    private Collection $chargeableServiceSummaryStubs;
    private Collection $isAttachedStubs;
    private bool $isChargeableServiceEnabledForMonth = false;
    private Collection $isFulfilledStubs;
    private Collection $summariesCreated;

    public function __construct()
    {
        $this->summariesCreated              = collect();
        $this->isAttachedStubs               = collect();
        $this->isFulfilledStubs              = collect();
        $this->chargeableServiceSummaryStubs = collect();
    }

    public function assertChargeableSummaryCreated(int $patientId, string $chargeableServiceCode, Carbon $month): void
    {
        PHPUnit::assertTrue(
            $this->wasChargeableSummaryCreated($patientId, $chargeableServiceCode, $month)
        );
    }

    public function assertChargeableSummaryNotCreated(int $patientId, string $chargeableServiceCode, Carbon $month): void
    {
        PHPUnit::assertFalse(
            1 === $this->summariesCreated->where('patientId', $patientId)
                ->where('chargeableServiceCode', $chargeableServiceCode)
                ->where('month', $month)
                ->count()
        );
    }

    public function fulfill(int $patientId, string $chargeableServiceCode, Carbon $month): ChargeablePatientMonthlySummary
    {
        //TODO: TEST
        $this->summariesCreated->push([
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

    public function getChargeablePatientSummary(int $patientId, string $chargeableServiceCode, Carbon $month)
    {
        return (bool) $this->chargeableServiceSummaryStubs
            ->where('chargeableServiceCode', $chargeableServiceCode)
            ->where('month', $month)
            ->where('patientId', $patientId)
            ->pluck('summary')
            ->first();
    }

    public function isAttached(int $patientId, string $chargeableServiceCode, Carbon $month): bool
    {
        if ($this->isAttachedStubs->isEmpty()) {
            return $this->wasChargeableSummaryCreated($patientId, $chargeableServiceCode, $month);
        }

        return (bool) $this->isAttachedStubs
            ->where('chargeableServiceCode', $chargeableServiceCode)
            ->where('month', $month)
            ->where('patientId', $patientId)
            ->pluck('showAsAttached')
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
            ->pluck('showAsFulfilled')
            ->first();
    }

    /**
     * @param ChargeablePatientMonthlySummary $chargeableServiceSummaryStub
     */
    public function setChargeableServiceSummaryStubs(ChargeablePatientMonthlySummaryStub ...$chargeableServiceSummaryStub): void
    {
        $this->chargeableServiceSummaryStubs = collect($chargeableServiceSummaryStub);
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

    public function store(int $patientId, string $chargeableServiceCode, Carbon $month, $requiresPatientConsent = false): ChargeablePatientMonthlySummary
    {
        $this->summariesCreated->push($array = [
            'patientId'                => $patientId,
            'chargeableServiceCode'    => $chargeableServiceCode,
            'month'                    => $month,
            'requires_patient_consent' => $requiresPatientConsent,
        ]);

        return new ChargeablePatientMonthlySummary($array);
    }

    private function wasChargeableSummaryCreated(int $patientId, string $chargeableServiceCode, Carbon $month)
    {
        return 1 === $this->summariesCreated->where('patientId', $patientId)
            ->where('chargeableServiceCode', $chargeableServiceCode)
            ->where('month', $month)
            ->count();
    }
}
