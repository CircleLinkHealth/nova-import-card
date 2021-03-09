<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Algorithms;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Domain\Patient\PatientMonthlyServiceTime;
use CircleLinkHealth\CcmBilling\Entities\BillingConstants;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\Debug\MeasureTime;
use CircleLinkHealth\NurseInvoices\Time\TimeSplitter;
use CircleLinkHealth\NurseInvoices\ValueObjects\PatientPayCalculationResult;
use CircleLinkHealth\NurseInvoices\ValueObjects\TimeRangeEntry;
use CircleLinkHealth\NurseInvoices\ValueObjects\TimeSlots;
use CircleLinkHealth\TimeTracking\Services\ActivityService;
use Facades\FriendsOfCat\LaravelFeatureFlags\Feature;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

abstract class NursePaymentAlgorithm
{
    const HOUR_IN_SECONDS                        = 3600;
    const MONTHLY_TIME_TARGET_2X_IN_SECONDS      = 2400;
    const MONTHLY_TIME_TARGET_3X_IN_SECONDS      = 3600;
    const MONTHLY_TIME_TARGET_IN_SECONDS         = 1200;
    const MONTHLY_TIME_TARGET_IN_SECONDS_FOR_PCM = 1800;

    protected bool $debug = false;

    protected Carbon $endDate;

    protected Nurse $nurseInfo;

    protected ?User $patient;

    /**
     * @var Collection|NurseCareRateLog[]
     */
    protected Collection $patientCareRateLogs;

    protected ?int $patientId;

    protected bool $practiceHasCcmPlus = false;

    protected Carbon $startDate;

    public function __construct(Nurse $nurseInfo, Collection $patientCareRateLogs, Carbon $startDate, Carbon $endDate, ?User $patient = null, bool $debug = false)
    {
        $this->debug               = $debug;
        $this->nurseInfo           = $nurseInfo;
        $this->patientCareRateLogs = $patientCareRateLogs;
        $this->startDate           = $startDate;
        $this->endDate             = $endDate;
        $this->patient             = $patient;
        if ($this->patient) {
            $this->patientId = $patient->id;
        }
    }

    abstract public function calculate(): PatientPayCalculationResult;

    protected function arrangeSlotsForRange(
        Collection $range,
        int $nurseInfoId,
        bool $isSuccessfulCall,
        string $logDate,
        TimeSlots $slots
    ) {
        if ($slots->towards20 || ($isSuccessfulCall && 'towards_20' === $slots->current)) {
            /** @var Collection $rangeTowards20 */
            $rangeTowards20 = $range->get(0, collect());
            $rangeTowards20->put($nurseInfoId, $this->getEntryForRange(
                $rangeTowards20,
                $nurseInfoId,
                $slots->towards20,
                $isSuccessfulCall,
                $logDate
            ));
            $range->put(0, $rangeTowards20);
        }
        if ($slots->after20 || ($isSuccessfulCall && 'after_20' === $slots->current)) {
            /** @var Collection $rangeAfter20 */
            $rangeAfter20 = $range->get(1, collect());
            $rangeAfter20->put($nurseInfoId, $this->getEntryForRange(
                $rangeAfter20,
                $nurseInfoId,
                $slots->after20,
                $isSuccessfulCall,
                $logDate
            ));
            $range->put(1, $rangeAfter20);
        }
        if ($slots->after40 || ($isSuccessfulCall && 'after_40' === $slots->current)) {
            /** @var Collection $rangeAfter40 */
            $rangeAfter40 = $range->get(2, collect());
            $rangeAfter40->put($nurseInfoId, $this->getEntryForRange(
                $rangeAfter40,
                $nurseInfoId,
                $slots->after40,
                $isSuccessfulCall,
                $logDate
            ));
            $range->put(2, $rangeAfter40);
        }
        if ($slots->after60 || ($isSuccessfulCall && 'after_60' === $slots->current)) {
            /** @var Collection $rangeAfter60 */
            $rangeAfter60 = $range->get(3, collect());
            $rangeAfter60->put($nurseInfoId, $this->getEntryForRange(
                $rangeAfter60,
                $nurseInfoId,
                $slots->after60,
                $isSuccessfulCall,
                $logDate
            ));
            $range->put(3, $rangeAfter60);
        }

        if ($slots->towards30 || ($isSuccessfulCall && 'towards_30' === $slots->current)) {
            /** @var Collection $rangeTowards30 */
            $rangeTowards30 = $range->get(0, collect());
            $rangeTowards30->put($nurseInfoId, $this->getEntryForRange(
                $rangeTowards30,
                $nurseInfoId,
                $slots->towards30,
                $isSuccessfulCall,
                $logDate
            ));
            $range->put(0, $rangeTowards30);
        }
        if ($slots->after30 || ($isSuccessfulCall && 'after_30' === $slots->current)) {
            /** @var Collection $rangeAfter30 */
            $rangeAfter30 = $range->get(1, collect());
            $rangeAfter30->put($nurseInfoId, $this->getEntryForRange(
                $rangeAfter30,
                $nurseInfoId,
                $slots->after30,
                $isSuccessfulCall,
                $logDate
            ));
            $range->put(1, $rangeAfter30);
        }

        return $range;
    }

    protected function getEntryForRange(
        Collection $range,
        int $nurseInfoId,
        int $newDuration,
        bool $successfulCall,
        string $logDate
    ): TimeRangeEntry {
        /** @var ?TimeRangeEntry $prev */
        $prev = null;
        if ($range->has($nurseInfoId)) {
            $prev = $range->get($nurseInfoId);
        }

        $duration          = $newDuration;
        $hasSuccessfulCall = $successfulCall;
        if ($prev) {
            if ( ! $hasSuccessfulCall && $prev->hasSuccessfulCall) {
                $hasSuccessfulCall = true;
            }

            $duration += $prev->duration;
        }

        return new TimeRangeEntry($duration, $hasSuccessfulCall, $logDate);
    }

    protected function getTimeSlotsForChargeableService(
        int $totalTimeBefore,
        int $duration,
        string $csCode
    ): TimeSlots {
        $splitter                  = new TimeSplitter();
        $splitFor30MinuteIntervals = ChargeableService::PCM === $csCode;
        $splitUpTo60Plus           = in_array($csCode, array_merge(ChargeableService::RPM_CODES, ChargeableService::CCM_CODES));

        return $splitter->split($totalTimeBefore, $duration, $splitFor30MinuteIntervals, $splitUpTo60Plus);
    }

    protected function getTotalTimeForMonth(string $csCode): int
    {
        $month = $this->startDate->copy()->startOfMonth();

        /** @var ChargeableService $cs */
        $cs = ChargeableService::cached()->firstWhere('code', '=', $csCode);
        if ( ! $cs) {
            return 0;
        }

        if (Feature::isEnabled(BillingConstants::BILLING_REVAMP_FLAG)) {
            return PatientMonthlyServiceTime::forChargeableServiceId($cs->id, $this->patientId, $month);
        }

        return app(ActivityService::class)->totalTimeForChargeableServiceId($this->patientId, $cs->id, $month);
    }

    protected function measureTimeAndLog(string $desc, $func)
    {
        if ( ! $this->debug) {
            return $func();
        }

        $patientId   = $this->patientId ?? '';
        $generalInfo = static::class."[$patientId]";
        $msg         = "$generalInfo-$desc";

        return MeasureTime::log($msg, $func);
    }

    protected function practiceHasCcmPlusCode(
        Practice $practice
    ) {
        return Cache::store('array')->rememberForever("ccm_plus_$practice->id", function () use ($practice) {
            return $practice->hasCCMPlusServiceCode();
        });
    }

    protected function separateTimeAccruedInRanges(Collection $patientCareRateLogs)
    {
        /**
         * CCM (+ CCM40, CCM60)
         * 0 => 0-20
         * 1 => 20-40
         * 2 => 40-60
         * 3 => 60+.
         *
         * BHI
         * 0 => 0-20
         * 1 => 20+.
         *
         * PCM
         * 0 => 0-30
         * 1 => 30+.
         *
         * RHC
         * 0 => 0-20
         * 1 => 20+.
         *
         * RPM (+ RPM40, RPM60)
         * 0 => 0-20
         * 1 => 20-40
         * 2 => 40-60
         * 3 => 60+.
         */
        $timeEntryPerCsCodePerRangePerNurseInfoId = collect();
        $chargeableServices                       = ChargeableService::cached();

        $patientCareRateLogs->each(function ($e) use ($timeEntryPerCsCodePerRangePerNurseInfoId, $chargeableServices) {
            $chargeableServiceId = $e['chargeable_service_id'];
            if ( ! $chargeableServiceId) {
                return;
            }

            $nurseInfoId = $e['nurse_id'];
            $isSuccessfulCall = $e['is_successful_call'];
            $duration = $e['increment'];
            $totalTimeBefore = $e['time_before'];

            // performed_at is a new field, we revert to created_at if it is null
            $logDate = ($e['performed_at'] ?? $e['created_at'])->toDateString();

            $csCode = $chargeableServices->where('id', '=', $chargeableServiceId)->first()->code;
            $slots = $this->getTimeSlotsForChargeableService($totalTimeBefore, $duration, $csCode);

            if (in_array($csCode, ChargeableService::CCM_PLUS_CODES)) {
                $csCode = ChargeableService::CCM;
            } elseif (in_array($csCode, ChargeableService::RPM_CODES)) {
                $csCode = ChargeableService::RPM;
            }

            /** @var Collection $range */
            $range = $timeEntryPerCsCodePerRangePerNurseInfoId->get($csCode, collect());
            $range = $this->arrangeSlotsForRange($range, $nurseInfoId, $isSuccessfulCall, $logDate, $slots);

            $timeEntryPerCsCodePerRangePerNurseInfoId->put($csCode, $range);
        });

        $timeEntryPerCsCodePerRangePerNurseInfoId->each(function (Collection $rangePerChargeableServiceCode) {
            $this->setSuccessfulCallBasedOnPreviousRange($rangePerChargeableServiceCode);
        });

        return $timeEntryPerCsCodePerRangePerNurseInfoId;
    }

    /**
     * If a range is in the same day as an other range with a successful call,
     * make sure that both have successful call as true
     * This covers the case where a care coach makes a call that spans over two ranges.
     */
    protected function setSuccessfulCallBasedOnPreviousRange(Collection $coll)
    {
        $nurseCallsInDays = collect();
        $coll->each(function (Collection $nurseRange) use ($nurseCallsInDays) {
            $nurseRange->each(function (TimeRangeEntry $range, string $nurseInfoId) use ($nurseCallsInDays) {
                if ( ! $range->hasSuccessfulCall) {
                    return;
                }
                $entry = $nurseCallsInDays->get($nurseInfoId, []);
                if ( ! in_array($range->lastLogDate, $entry)) {
                    $entry[] = $range->lastLogDate;
                }
                $nurseCallsInDays->put($nurseInfoId, $entry);
            });
        });
        $coll->each(function (Collection $nurseRange, $rangeIndex) use ($nurseCallsInDays) {
            $nurseRange->each(function (TimeRangeEntry $range, string $nurseInfoId) use ($nurseCallsInDays) {
                $entry = $nurseCallsInDays->get($nurseInfoId, []);
                if (in_array($range->lastLogDate, $entry)) {
                    $range->hasSuccessfulCall = true;
                }
            });
        });
    }
}
