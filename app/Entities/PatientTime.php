<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Entities;

use App\Constants;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use Illuminate\Support\Collection;

/**
 * todo: move from ValueObjects folder
 * Class PatientTime.
 */
class PatientTime
{
    private Collection $times;

    /**
     * PatientTime constructor.
     */
    public function __construct()
    {
        $this->times = collect();
    }

    public function getTime(string $csCode): int
    {
        return $this->times->get($csCode, 0);
    }

    public function isFulFilled(string $csCode): bool
    {
        $time = $this->getTime($csCode);
        switch ($csCode) {
            case ChargeableService::CCM:
                return $time >= Constants::MONTHLY_BILLABLE_TIME_TARGET_IN_SECONDS;
            case ChargeableService::CCM_PLUS_40:
                return $time >= Constants::MONTHLY_BILLABLE_CCM_40_TIME_TARGET_IN_SECONDS;
            case ChargeableService::CCM_PLUS_60:
                return $time >= Constants::MONTHLY_BILLABLE_CCM_60_TIME_TARGET_IN_SECONDS;
            case ChargeableService::PCM:
                return $time >= Constants::MONTHLY_BILLABLE_PCM_TIME_TARGET_IN_SECONDS;
        }

        return false;
    }

    public function setTime(string $csCode, int $time): PatientTime
    {
        $this->times->put($csCode, $time);

        return $this;
    }
}
