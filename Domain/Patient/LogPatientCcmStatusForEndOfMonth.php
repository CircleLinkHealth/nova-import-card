<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;

class LogPatientCcmStatusForEndOfMonth
{
    protected string $ccmStatus;
    protected Carbon $month;
    protected int $patientUserId;

    public function __construct(int $patientUserId, string $ccmStatus, Carbon $month)
    {
        $this->patientUserId = $patientUserId;
        $this->ccmStatus     = $ccmStatus;
        $this->month         = $month;
    }

    public static function create(int $patientUserId, string $ccmStatus, Carbon $month): void
    {
        (new static($patientUserId, $ccmStatus, $month))->log();
    }

    public function log()
    {
        //allow saving the status at the last day of month (same as month taken)
        //or within the first 4 hours of the new month
        //disregard future dates
        //for past dates, attempt to get ccm status from revision tables.
    }
}
