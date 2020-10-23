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
        return app(PatientMonthlyServiceTime::class)
            ->setSummaries($patientId, $month)
            ->getTimeforServices([ChargeableService::BHI], false);
    }
    
    public static function ccm(int $patientId, Carbon $month): int
    {
        return app(PatientMonthlyServiceTime::class)
            ->setSummaries($patientId, $month)
            ->getTimeforServices([ChargeableService::CCM, ChargeableService::CCM_PLUS_40, ChargeableService::CCM_PLUS_60]);
    }
    
    public static function rpm(int $patientId, Carbon $month): int
    {
        return app(PatientMonthlyServiceTime::class)
            ->setSummaries($patientId, $month)
            ->getTimeforServices([ChargeableService::RPM, ChargeableService::RPM40]);
    }

    public static function bhi(int $patientId, Carbon $month): int
    {
        return app(PatientMonthlyServiceTime::class)
            ->setSummaries($patientId, $month)
            ->getTimeforServices([ChargeableService::BHI]);
    }

    public function getTimeforServices(array $chargeableServiceCodes, $include = true): int
    {
        if ($include) {
            return $this->summaries->whereIn('chargeable_service_code', $chargeableServiceCodes)
                ->sum('total_time') ?? 0;
        }

        return $this->summaries
            ->whereNotIn('chargeable_service_code', $chargeableServiceCodes)
            ->sum('total_time') ?? 0;
    }

    private function setSummaries(int $patientId, Carbon $month): self
    {
        $this->summaries = $this->repo->getChargeablePatientSummaries($patientId, $month);

        return  $this;
    }
}
