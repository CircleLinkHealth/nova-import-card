<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Entities;

use App\Constants;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
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

    public static function getForPatient(User $patient, \Illuminate\Database\Eloquent\Collection $chargeableServices): PatientTime
    {
        //todo: this should be done with the new ChargeablePatientMonthlySummaryView model
        $result = new PatientTime();
        if ( ! $patient) {
            return $result;
        }

        $ccmTime = $patient->getCcmTime();
        $bhiTime = $patient->getBhiTime();
        if ($ccmTime > 0) {
            if ($patient->isPcm()) {
                /** @var ChargeableService $ccmCs */
                $pcmCs = $chargeableServices->firstWhere('code', '=', ChargeableService::PCM);
                if ($pcmCs) {
                    $result->setTime($pcmCs->code, $ccmTime);
                }
            } elseif ( ! $patient->isCcmPlus()) {
                /** @var ChargeableService $ccmCs */
                $ccmCs = $chargeableServices->firstWhere('code', '=', ChargeableService::CCM);
                if ($ccmCs) {
                    $result->setTime($ccmCs->code, $ccmTime);
                }
            } else {
                /** @var ChargeableService $ccmCs */
                $ccmCs = $chargeableServices->firstWhere('code', '=', ChargeableService::CCM);
                if ($ccmCs) {
                    $result->setTime($ccmCs->code, $ccmTime > Constants::MONTHLY_BILLABLE_TIME_TARGET_IN_SECONDS ? Constants::MONTHLY_BILLABLE_TIME_TARGET_IN_SECONDS : $ccmTime);
                }

                if ($ccmTime > Constants::MONTHLY_BILLABLE_TIME_TARGET_IN_SECONDS) {
                    /** @var ChargeableService $ccm40Cs */
                    $ccm40Cs = $chargeableServices->firstWhere('code', '=', ChargeableService::CCM_PLUS_40);
                    if ($ccm40Cs) {
                        $time = $ccmTime > Constants::MONTHLY_BILLABLE_CCM_40_TIME_TARGET_IN_SECONDS ? Constants::MONTHLY_BILLABLE_CCM_40_TIME_TARGET_IN_SECONDS : $ccmTime - Constants::MONTHLY_BILLABLE_TIME_TARGET_IN_SECONDS;
                        $result->setTime($ccm40Cs->code, $time);
                    }
                }
                if ($ccmTime > Constants::MONTHLY_BILLABLE_CCM_40_TIME_TARGET_IN_SECONDS) {
                    /** @var ChargeableService $ccm60Cs */
                    $ccm60Cs = $chargeableServices->firstWhere('code', '=', ChargeableService::CCM_PLUS_60);
                    if ($ccm60Cs) {
                        $time = $ccmTime - Constants::MONTHLY_BILLABLE_CCM_40_TIME_TARGET_IN_SECONDS;
                        $result->setTime($ccm60Cs->code, $time);
                    }
                }
            }
        }
        if ($bhiTime > 0) {
            /** @var ChargeableService $bhiCs */
            $bhiCs = $chargeableServices->firstWhere('code', '=', ChargeableService::BHI);
            if ($bhiCs) {
                $result->setTime($bhiCs->code, $bhiTime);
            }
        }

        return $result;
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
