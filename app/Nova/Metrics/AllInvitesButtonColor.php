<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Metrics;

use CircleLinkHealth\Customer\EnrollableInvitationLink\EnrollableInvitationLink;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class AllInvitesButtonColor extends Partition
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
        return $this->count($request, $this->queryEnrolleesEnrolled(), 'button_color', \DB::raw('DISTINCT(invitationable_id)'))->colors([
            'Green' => '#4baf50',
            'Red'   => '#b1284c',
        ])->label(function ($value) {
            switch ($value) {
                case '#b1284c':
                    return 'Red';
                default:
                    return 'Green';
            }
        });
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'all-invites-button-color';
    }

    private function queryEnrolleesEnrolled()
    {
        return EnrollableInvitationLink::whereIn('invitationable_id', function ($q) {
            $q->select('id')->from('enrollees')->where('practice_id', '=', $this->practiceId);
        })->where('invitationable_type', Enrollee::class);
    }
}
