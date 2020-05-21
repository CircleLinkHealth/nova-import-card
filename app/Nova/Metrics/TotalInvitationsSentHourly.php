<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Metrics;

use CircleLinkHealth\Core\Entities\DatabaseNotification;
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
        // return now()->addMinutes(1);;
    }

    /**
     * Calculate the value of the metric.
     *
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        return $this->countByHours($request, $this->queryEnrolleesEnrolled());
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
        return DatabaseNotification::whereIn('notifiable_id', function ($q) {
            $q->select('user_id')->from('enrollees')->where('practice_id', '=', $this->practiceId);
        })->whereIn('notifiable_type', [\App\User::class, \CircleLinkHealth\Customer\Entities\User::class])->distinct('notifiable_id')->where('type', 'like', '%SendEnrollmentEmail%');
    }
}
