<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Metrics;

use App\Http\Controllers\Enrollment\SelfEnrollmentController;
use CircleLinkHealth\Customer\EnrollableInvitationLink\EnrollableInvitationLink;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
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
            'Green' => SelfEnrollmentController::DEFAULT_BUTTON_COLOR,
            'Red'   => SelfEnrollmentController::RED_BUTTON_COLOR,
            'Blue'  => SelfEnrollmentController::BLUE_BUTTON_COLOR,
        ])->label(function ($value) {
            switch ($value) {
                case SelfEnrollmentController::RED_BUTTON_COLOR:
                    return 'Red';
                case SelfEnrollmentController::BLUE_BUTTON_COLOR:
                    return 'Blue';
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
