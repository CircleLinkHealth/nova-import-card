<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientProcessorEloquentRepository;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert as PHPUnit;

class Eloquent implements PatientProcessorEloquentRepository
{
    private Collection $collection;

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

    public function getChargeablePatientSummaries(int $patientId, Carbon $month)
    {
        // TODO: Implement getChargeablePatientSummaries() method.
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
