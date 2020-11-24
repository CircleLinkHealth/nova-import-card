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

    public static function forChargeableServiceId(int $chargeableServiceId, int $patientId, Carbon $month): int
    {
        $allCs = ChargeableService::cached();
        /** @var ChargeableService $cs */
        $cs = $allCs->firstWhere('id', '=', $chargeableServiceId);
        if ( ! $cs) {
            return 0;
        }
        switch ($cs->code) {
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
            case ChargeableService::GENERAL_CARE_MANAGEMENT:
                return PatientMonthlyServiceTime::rhc($patientId, $month);
            default:
                return 0;
        }
    }

    public static function pcm(int $patientId, Carbon $month): int
    {
        return app(PatientMonthlyServiceTime::class)
            ->setSummaries($patientId, $month)
            ->getTimeForServices([ChargeableService::PCM]);
    }

    public static function rhc(int $patientId, Carbon $month): int
    {
        return app(PatientMonthlyServiceTime::class)
            ->setSummaries($patientId, $month)
            ->getTimeForServices([ChargeableService::GENERAL_CARE_MANAGEMENT]);
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
        $csIds = ChargeableService::cached()
            ->whereIn('code', $chargeableServiceCodes)
            ->pluck('id')
            ->toArray();

        return $this->getTimeForServiceIds($csIds, $include);
    }

    private function setSummaries(int $patientId, Carbon $month): self
    {
        $this->summaries = $this->repo->getChargeablePatientSummaries($patientId, $month);

        return  $this;
    }
}
