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
            ->getTimeForServices([ChargeableService::BHI], false);
    }

    public static function bhi(int $patientId, Carbon $month): int
    {
        return app(PatientMonthlyServiceTime::class)
            ->setSummaries($patientId, $month)
            ->getTimeForServices([ChargeableService::BHI]);
    }

    public static function ccm(int $patientId, Carbon $month): int
    {
        return app(PatientMonthlyServiceTime::class)
            ->setSummaries($patientId, $month)
            ->getTimeForServices([ChargeableService::CCM, ChargeableService::CCM_PLUS_40, ChargeableService::CCM_PLUS_60]);
    }

    public static function forChargeableServiceCode(string $chargeableServiceCode, int $patientId, Carbon $month): int
    {
        switch ($chargeableServiceCode) {
            case ChargeableService::CCM:
            case ChargeableService::CCM_PLUS_40:
            case ChargeableService::CCM_PLUS_60:
                return PatientMonthlyServiceTime::ccm($patientId, $month);
            case ChargeableService::BHI:
                return PatientMonthlyServiceTime::bhi($patientId, $month);
            case ChargeableService::PCM:
                return PatientMonthlyServiceTime::pcm($patientId, $month);
            case ChargeableService::RPM:
            case ChargeableService::RPM40:
                return PatientMonthlyServiceTime::rpm($patientId, $month);
            default:
                return 0;
        }
    }

    public static function forChargeableServiceIds(array $chargeableServiceIds, int $patientId, Carbon $month): int
    {
        return app(PatientMonthlyServiceTime::class)
            ->setSummaries($patientId, $month)
            ->getTimeForServiceIds($chargeableServiceIds);
    }

    public static function pcm(int $patientId, Carbon $month): int
    {
        return app(PatientMonthlyServiceTime::class)
            ->setSummaries($patientId, $month)
            ->getTimeForServices([ChargeableService::PCM]);
    }

    public static function rpm(int $patientId, Carbon $month): int
    {
        return app(PatientMonthlyServiceTime::class)
            ->setSummaries($patientId, $month)
            ->getTimeForServices([ChargeableService::RPM, ChargeableService::RPM40]);
    }

    private function getTimeForServiceIds(array $chargeableServiceIds, $include = true): int
    {
        if ($include) {
            return $this->summaries->whereIn('chargeable_service_id', $chargeableServiceIds)
                ->sum('total_time') ?? 0;
        }

        return $this->summaries
            ->whereNotIn('chargeable_service_id', $chargeableServiceIds)
            ->sum('total_time') ?? 0;
    }

    private function getTimeForServices(array $chargeableServiceCodes, $include = true): int
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
