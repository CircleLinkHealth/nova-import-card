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
use CircleLinkHealth\CcmBilling\Traits\PropagatesSequence;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientProblemForProcessing;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use Illuminate\Support\Collection;

class CCM implements PatientChargeableServiceProcessor
{
    use PropagatesSequence;

    private PatientProcessorEloquentRepository $repo;

    public function attach(int $patientId, Carbon $chargeableMonth): ChargeablePatientMonthlySummary
    {
        return $this->repo()->store($patientId, $this->code(), $chargeableMonth);
    }

    public function code(): string
    {
        return ChargeableService::CCM;
    }

    public function fulfill(int $patientId, Carbon $chargeableMonth): ChargeablePatientMonthlySummary
    {
        $summary = $this->repo()->fulfill($patientId, $this->code(), $chargeableMonth);

        $this->attachNext($patientId, $chargeableMonth);

        return $summary;
    }

    public function isAttached(int $patientId, Carbon $chargeableMonth): bool
    {
        // TODO: Implement isAttached() method.
    }

    public function isFulfilled(int $patientId, Carbon $chargeableMonth): bool
    {
        // TODO: Implement isFulfilled() method.
    }

    public function minimumNumberOfCalls(): int
    {
        return 1;
    }

    public function minimumNumberOfProblems(): int
    {
        return 2;
    }

    public function minimumTimeInSeconds(): int
    {
        return Constants::TWENTY_MINUTES_IN_SECONDS;
    }

    public function next(): PatientChargeableServiceProcessor
    {
        return new CCM40();
    }

    public function processBilling(int $patientId, Carbon $chargeableMonth)
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

    public function shouldAttach(Carbon $chargeableMonth, PatientProblemForProcessing ...$patientProblems): bool
    {
        return collect($patientProblems)->filter(function (PatientProblemForProcessing $problemToProcess) {
            return in_array($this->code(), $problemToProcess->getServiceCodes());
        })
            ->count() >= $this->minimumNumberOfProblems();
    }

    public function shouldFulfill(int $patientId, Carbon $chargeableMonth): bool
    {
        // TODO: Implement shouldFulfill() method.
    }
}
