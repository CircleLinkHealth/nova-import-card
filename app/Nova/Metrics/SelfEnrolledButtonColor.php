<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Metrics;

use CircleLinkHealth\Customer\EnrollableInvitationLink\EnrollableInvitationLink;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class SelfEnrolledButtonColor extends Partition
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
        return 'self-enrolled-btn-color';
    }

    private function queryEnrolleesEnrolled()
    {
        return EnrollableInvitationLink::select(['button_color', 'invitationable_id'])->whereIn('invitationable_id', function ($q) {
            $q->select('id')->from('enrollees')->where('practice_id', '=', $this->practiceId)->where('auto_enrollment_triggered', '=', true)->where('status', '=', Enrollee::ENROLLED);
        })->where('invitationable_type', Enrollee::class);
    }
}
