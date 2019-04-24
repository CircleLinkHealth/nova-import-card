<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Metrics;

use App\Constants;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Value;
use Venturecraft\Revisionable\Revision;

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
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function calculate(Request $request)
    {
        return $this->count(
            $request,
            $this->patientsOverTargetQuery('bhi_time')
        );
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            1     => '24 hours',
            2     => '48 hours',
            7     => '7 Days',
            60    => '60 Days',
            365   => '365 Days',
            'MTD' => 'Month To Date',
            'QTD' => 'Quarter To Date',
            'YTD' => 'Year To Date',
        ];
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'patients-over-twenty-minutes';
    }

    private function patientsOverTargetQuery($key)
    {
        return Revision::query()
            ->where('revisionable_type', PatientMonthlySummary::class)
            ->where('key', $key)
            ->where(
                'old_value',
                '<',
                Constants::MONTHLY_BILLABLE_TIME_TARGET_IN_SECONDS
                       )
            ->where(
                'new_value',
                '>=',
                Constants::MONTHLY_BILLABLE_TIME_TARGET_IN_SECONDS
                       );
    }
}
