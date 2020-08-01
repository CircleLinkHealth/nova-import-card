<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Metrics;

use CircleLinkHealth\Revisionable\Entities\Revision;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Value;

class PatientsOverTargetBhiTime extends Value
{
    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return \DateInterval|\DateTimeInterface|float|int
     */
    public function cacheFor()
    {
        return now()->addMinutes(5);
    }

    /**
     * Calculate the value of the metric.
     *
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $revisionsTable = (new Revision())->getTable();

        return $this->count(
            $request,
            (new PatientsOverTargetCcmTime())->patientsOverTargetQuery('bhi_time'),
            null,
            "$revisionsTable.created_at"
        );
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return (new PatientsOverTargetCcmTime())->ranges();
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'patients-over-target-bhi-time';
    }
}
