<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Metrics;

use CircleLinkHealth\Core\Entities\DatabaseNotification;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Trend;

class InvitationsSent extends Trend
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
        // return now()->addMinutes(1);;
    }

    /**
     * Calculate the value of the metric.
     *
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        return $this->countByDays($request, $this->queryEnrolleesEnrolled());
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            1  => '1 Day',
            2  => '2 Days',
            7  => '7 Days',
            14 => '14 Days',
            30 => '30 Days',
            60 => '60 Days',
            90 => '90 Days',
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
        return DatabaseNotification::whereIn('notifiable_id', function ($q) {
            $q->select('user_id')->from('enrollees')->where('practice_id', '=', $this->practiceId);
        })->whereIn('notifiable_type', [\App\User::class, \CircleLinkHealth\Customer\Entities\User::class])->distinct('notifiable_id')->where('type', 'like', '%SendEnrollmentEmail%');
    }
}
