<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use App\Constants;
use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientChargeableServiceProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PatientProcessorEloquentRepository;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\ChargeableService;

class BHI implements PatientChargeableServiceProcessor
{
    private PatientProcessorEloquentRepository $repo;

    public function attach(int $patientId, Carbon $monthYear): ChargeablePatientMonthlySummary
    {
        return $this->repo()->store($patientId, $this->code(), $monthYear);
    }

    public function code(): string
    {
        return ChargeableService::BHI;
    }

    public function fulfill(int $patientId, Carbon $monthYear): ChargeablePatientMonthlySummary
    {
        // TODO: Implement fulfill() method.
    }

    public function isAttached(int $patientId, Carbon $monthYear): bool
    {
        // TODO: Implement isAttached() method.
    }

    public function isFulfilled(int $patientId, Carbon $monthYear): bool
    {
        // TODO: Implement isFulfilled() method.
    }

    public function minimumNumberOfCalls(): int
    {
        return 1;
    }

    public function minimumNumberOfProblems(): int
    {
        return 1;
    }

    public function minimumTimeInSeconds(): int
    {
        return Constants::TWENTY_MINUTES_IN_SECONDS;
    }

    public function processBilling(int $patientId, Carbon $monthYear)
    {
        // TODO: Implement processBilling() method.
    }

    public function repo(): PatientProcessorEloquentRepository
    {
        if ( ! isset($this->repo)) {
            $this->repo = app(PatientProcessorEloquentRepository::class);
        }

        return $this->repo;
    }

    public function shouldAttach($patientProblems, Carbon $monthYear): bool
    {
        return $patientProblems->where('code', $this->code())->count() >= $this->minimumNumberOfProblems();
    }

    public function shouldFulfill(int $patientId, Carbon $monthYear): bool
    {
        // TODO: Implement shouldFulfill() method.
    }
}
