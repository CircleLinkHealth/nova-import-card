<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\EndOfMonthCcmStatusLog;
use CircleLinkHealth\Customer\Entities\PatientCcmStatusRevision;

class LogPatientCcmStatusForEndOfMonth
{
    const CCM_STATUS_NOT_SET = 'not set';
    const HOUR_WINDOW_FOR_CURRENT_CCM_STATUS_UPDATE_ON_NEW_MONTH = 6;
    const HOUR_WINDOW_FOR_LAST_HOURS_OF_MONTH                    = 2;

    protected ?string $ccmStatus;
    protected Carbon $month;
    protected int $patientUserId;

    public function __construct(int $patientUserId, ?string $ccmStatus, Carbon $month)
    {
        $this->patientUserId = $patientUserId;
        $this->ccmStatus     = $ccmStatus;
        $this->month         = $month;
    }

    public static function create(int $patientUserId, ?string $ccmStatus, Carbon $month): void
    {
        (new static($patientUserId, $ccmStatus, $month))->log();
    }

    public function log(): void
    {
        if ($this->shouldLogCurrentStatus()) {
            $this->logStatus($this->getCurrentCcmStatus());

            return;
        }

        $this->logStatus($this->getStatusUsingRevisions());
    }

    private function getStatusUsingRevisions(): string
    {
        $revisions = PatientCcmStatusRevision::whereBetween('created_at', [
            $this->month->copy()->endOfMonth()->endOfDay(),
            Carbon::now(),
        ])
            ->where('action', '=', PatientCcmStatusRevision::ACTION_UPDATE)
            ->where('patient_user_id', '=', $this->patientUserId)
            ->orderBy('created_at')
            ->get();

        if ($revisions->isEmpty()) {
            return $this->getCurrentCcmStatus();
        }

        return $revisions->first()->old_value;
    }
    
    private function getCurrentCcmStatus():string
    {
        return $this->ccmStatus ?? self::CCM_STATUS_NOT_SET;
    }

    private function logStatus(string $ccmStatus): void
    {
        EndOfMonthCcmStatusLog::updateOrCreate(
            [
                'patient_user_id'  => $this->patientUserId,
                'chargeable_month' => $this->month->copy()->startOfMonth(),
            ],
            [
                'closed_ccm_status' => $ccmStatus,
            ]
        );
    }

    private function nowIsEndOfInputtedMonth(): bool
    {
        $endOfMonthEndOfDay            = $this->month->copy()->endOfMonth()->endOfDay();
        $startPointForLastHoursOfMonth = $endOfMonthEndOfDay->subHours(self::HOUR_WINDOW_FOR_LAST_HOURS_OF_MONTH);

        return $startPointForLastHoursOfMonth->hoursUntil($endOfMonthEndOfDay)->contains(Carbon::now());
    }

    private function nowIsWithinEndOfInputtedMonthAndAcceptableWindow(): bool
    {
        $endOfMonthEndOfDay               = $this->month->copy()->endOfMonth()->endOfDay();
        $endPointForFirstHoursOfNextMonth = $endOfMonthEndOfDay->copy()->addHours(self::HOUR_WINDOW_FOR_CURRENT_CCM_STATUS_UPDATE_ON_NEW_MONTH);

        return $endOfMonthEndOfDay->hoursUntil($endPointForFirstHoursOfNextMonth)->contains(Carbon::now());
    }

    private function shouldLogCurrentStatus(): bool
    {
        if ($this->nowIsEndOfInputtedMonth()) {
            return true;
        }

        if ($this->nowIsWithinEndOfInputtedMonthAndAcceptableWindow()) {
            return true;
        }

        return false;
    }
}
