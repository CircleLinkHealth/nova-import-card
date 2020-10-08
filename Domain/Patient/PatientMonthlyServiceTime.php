<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use Illuminate\Database\Eloquent\Collection;

class PatientMonthlyServiceTime
{
    protected PatientServiceProcessorRepository $repo;
    protected Collection $summaries;

    public function __construct(PatientServiceProcessorRepository $repo)
    {
        $this->repo = $repo;
    }

    public static function allNonBhi(int $patientId, Carbon $month): int
    {
        return (new static(app(PatientServiceProcessorRepository::class)))
            ->setSummaries($patientId, $month)
            ->forServices([ChargeableService::BHI], false);
    }

    public static function bhi(int $patientId, Carbon $month): int
    {
        return (new static(app(PatientServiceProcessorRepository::class)))
            ->forServices([ChargeableService::BHI]);
    }

    public function forServices(array $chargeableServiceCodes, $include = true): int
    {
        if ($include) {
            return $this->summaries->whereIn('chargeable_service_code', $chargeableServiceCodes)
                ->sum('total_time');
        }

        return $this->summaries
            ->whereNotIn('chargeable_service_code', $chargeableServiceCodes)
            ->sum('total_time');
    }

    private function setSummaries(int $patientId, Carbon $month): self
    {
        $this->summaries = $this->repo->getChargeablePatientSummaries($patientId, $month);

        return  $this;
    }
}
