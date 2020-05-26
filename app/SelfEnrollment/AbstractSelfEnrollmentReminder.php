<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment;

use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\AppConfig;
use Illuminate\Foundation\Bus\PendingDispatch;

abstract class AbstractSelfEnrollmentReminder extends AbstractSelfEnrollableUserIterator
{
    /**
     * @var Carbon
     */
    protected $end;
    /**
     * @var int|null
     */
    protected $practiceId;
    /**
     * @var Carbon
     */
    protected $start;

    private function __construct(Carbon $endDate, Carbon $startDate, ?int $practiceId = null)
    {
        $this->end        = $endDate;
        $this->start      = $startDate;
        $this->practiceId = $practiceId;
    }

    public static function createForInvitesSentTwoDaysAgo()
    {
        $testingMode = filter_var(AppConfig::pull('testing_enroll_sms', true), FILTER_VALIDATE_BOOLEAN);

        if ($testingMode) {
            $practiceId = Helpers::getDemoPractice()->id;
            $startDate  = now()->startOfDay();
            $endDate    = $startDate->copy()->endOfDay();
        } else {
            $practiceId = null;
            $startDate  = now()->copy()->subHours(48)->startOfDay();
            $endDate    = $startDate->copy()->endOfDay();
        }

        return new static($endDate, $startDate, $practiceId);
    }

    public function dispatchToQueue()
    {
        return new PendingDispatch($this);
    }
}
