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
    protected $dateInviteSent;
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

    public function __construct(Carbon $dateInviteSent, ?int $practiceId = null)
    {
        $this->practiceId     = $practiceId;
        $this->dateInviteSent = $dateInviteSent;
    }

    public static function createForInvitesSentTwoDaysAgo()
    {
        $testingMode = filter_var(AppConfig::pull('testing_enroll_sms', true), FILTER_VALIDATE_BOOLEAN);

        if ($testingMode) {
            $practiceId     = Helpers::getDemoPractice()->id;
            $dateInviteSent = now()->startOfDay();
        } else {
            $practiceId     = null;
            $dateInviteSent = now()->copy()->subHours(48)->startOfDay();
        }

        return new static($dateInviteSent, $practiceId);
    }

    public function dispatchToQueue()
    {
        return new PendingDispatch($this);
    }
}
