<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummaryView;
use CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Patient\Stubs\ChargeablePatientMonthlySummaryStub;
use CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Patient\Stubs\IsAttachedStub;
use CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Patient\Stubs\IsFulfilledStub;
use CircleLinkHealth\Customer\Entities\ChargeableService;
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
            'patient_id'            => $patientId,
            'chargeable_service_id' => $chargeableServiceCode,
            'chargeable_month'      => $month,
            'is_fulfilled'          => true,
        ]);

        return new ChargeablePatientMonthlySummary();
    }

    public function getChargeablePatientSummaries(int $patientId, Carbon $month)
    {
        // TODO: Implement getChargeablePatientSummaries() method.
    }

    public function getChargeablePatientSummary(int $patientId, string $chargeableServiceCode, Carbon $month): ?ChargeablePatientMonthlySummaryView
    {
        return new ChargeablePatientMonthlySummaryView($this->summariesCreated
            ->where('chargeable_service_id', $this->chargeableServiceCodeIds()[$chargeableServiceCode])
            ->where('chargeable_month', $month)
            ->where('patient_id', $patientId)
            ->first());
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

    public function setPatientConsented(int $patientId, string $chargeableServiceCode, Carbon $month): ChargeablePatientMonthlySummary
    {
        $array               = [];
        $chargeableServiceId = $this->chargeableServiceCodeIds()[$chargeableServiceCode];
        //todo: untangify
        if (
            $this->summariesCreated->where('patient_id', $patientId)
                ->where('chargeable_service_id', $chargeableServiceId)
                ->where('month', $month)
                ->where('requires_patient_consent', true)
                ->isNotEmpty()
        ) {
            $this->summariesCreated->map(function ($summary) use ($patientId, $chargeableServiceId, $month, &$array) {
                if ($summary['patient_id'] === $patientId && $summary['chargeable_service_id'] === $this->chargeableServiceCodeIds()[$chargeableServiceId] && $month->equalTo($summary['month'])) {
                    $array = $summary['requires_patient_consent'] = false;
                }

                return $summary;
            });
        } else {
            $this->summariesCreated->push($array = [
                'patient_id'               => $patientId,
                'chargeable_service_id'    => $chargeableServiceId,
                'month'                    => $month,
                'requires_patient_consent' => false,
            ]);
        }

        return new ChargeablePatientMonthlySummary($array);
    }

    public function store(int $patientId, string $chargeableServiceCode, Carbon $month, $requiresPatientConsent = false): ChargeablePatientMonthlySummary
    {
        $this->summariesCreated->push($array = [
            'patient_id'               => $patientId,
            'chargeable_service_id'    => $this->chargeableServiceCodeIds()[$chargeableServiceCode],
            'chargeable_month'         => $month,
            'requires_patient_consent' => $requiresPatientConsent,
        ]);

        return new ChargeablePatientMonthlySummary($array);
    }

    private function chargeableServiceCodeIds(): array
    {
        return [
            ChargeableService::CCM            => 1,
            ChargeableService::BHI            => 2,
            ChargeableService::CCM_PLUS_40    => 3,
            ChargeableService::CCM_PLUS_60    => 4,
            ChargeableService::PCM            => 5,
            ChargeableService::AWV_INITIAL    => 6,
            ChargeableService::AWV_SUBSEQUENT => 7,
        ];
    }

    private function wasChargeableSummaryCreated(int $patientId, string $chargeableServiceCode, Carbon $month)
    {
        return 1 === $this->summariesCreated->where('patientId', $patientId)
            ->where('chargeableServiceCode', $chargeableServiceCode)
            ->where('month', $month)
            ->count();
    }
}
