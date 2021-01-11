<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Metrics;

use CircleLinkHealth\Customer\EnrollableInvitationLink\EnrollableInvitationLink;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Trend;

class TotalInvitationsSentHourly extends Trend
{
    /**
     * @var int
     */
    private $practiceId;

    public function __construct(int $practiceId)
    {
        $this->practiceId = $practiceId;
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return \DateInterval|\DateTimeInterface|float|int
     */
    public function cacheFor()
    {
//        return now()->addMinutes(1);
    }

    /**
     * Calculate the value of the metric.
     *
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        return $this->aggregate($request, $this->queryEnrolleesEnrolled(), self::BY_HOURS, 'count', \DB::raw('DISTINCT(invitationable_id)'), 'created_at');
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            48  => '2 Days',
            72  => '3 Days',
            96  => '4 Days',
            168 => '7 Days',
            336 => '14 Days',
            720 => '30 Days',
        ];
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'invitations-sent';
    }

    private function queryEnrolleesEnrolled()
    {
        return EnrollableInvitationLink::whereIn('invitationable_id', function ($q) {
            $q->select('id')->from('enrollees')->where('practice_id', '=', $this->practiceId);
        })->where('invitationable_type', Enrollee::class);
    }
}
