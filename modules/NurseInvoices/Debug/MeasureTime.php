<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Debug;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MeasureTime
{
    public static function log(string $desc, $func)
    {
        /** @var Carbon $startTime */
        $startTime = now();

        $result = $func();

        /** @var Carbon $endTime */
        $endTime  = now();
        $ms       = $endTime->diffInMilliseconds($startTime);
        $startStr = $startTime->toTimeString();
        $endStr   = $endTime->toTimeString();
        Log::debug("NurseInvoices-$desc: $ms ms | Start: $startStr | End: $endStr");

        return $result;
    }
}
