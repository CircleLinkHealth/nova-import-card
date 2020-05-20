<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Metrics;

use CircleLinkHealth\Customer\EnrollableInvitationLink\EnrollableInvitationLink;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class SelfEnrolledPatientTotal extends Value
{
    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return \DateInterval|\DateTimeInterface|float|int
     */
    public function cacheFor()
    {
        return now()->addMinutes(2);
    }

    /**
     * Calculate the value of the metric.
     *
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        return $this->count($request, $this->queryEnrolleesEnrolled());
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            1       => '1 Day',
            2       => '2 Days',
            7       => '7 Days',
            14      => '14 Days',
            30      => '30 Days',
            60      => '60 Days',
            365     => '365 Days',
            'TODAY' => 'Today',
            'MTD'   => 'Month To Date',
            'QTD'   => 'Quarter To Date',
            'YTD'   => 'Year To Date',
        ];
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'self-enrolled-patient-count';
    }

    private function queryEnrolleesEnrolled()
    {
        return EnrollableInvitationLink::whereIn('invitationable_id', function ($q) {
            $q->select('id')->from('enrollees')->where('practice_id', '=', 232)->where('auto_enrollment_triggered', '=', true)->where('status', '=', Enrollee::ENROLLED);
        })->where('invitationable_type', Enrollee::class)->distinct('invitationable_id');
    }
}
