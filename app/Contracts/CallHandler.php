<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

use App\Algorithms\Calls\NextCallCalculator\CallHandlerResponse;

interface CallHandler
{
    public function getNextCallDate(
        int $patientId,
        int $ccmTimeInSeconds,
        int $currentWeekOfMonth,
        int $successfulCallsThisMonth,
        int $patientPreferredNumberOfMonthlyCalls
    ): CallHandlerResponse;
}
