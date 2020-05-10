<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Events\AutoEnrollableCollected;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SelfEnrollmentUnreachablePatients implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * @var int
     */
    private $amount;
    /**
     * @var int
     */
    private $practiceId;

    /**
     * Create a new job instance.
     */
    public function __construct(
        int $amount,
        int $practiceId
    ) {
        $this->amount     = $amount;
        $this->practiceId = $practiceId;
    }

    /**
     * NOTE: "whereDoesntHave" makes sure we dont invite Unreachable/Non responded - Users (Patients) second time.
     *
     * @return void
     */
    public function handle()
    {
        $enrollableUnreachablePatient = array_slice($this->getUnreachablePatients($this->practiceId), 0, $this->amount);
        if ( ! empty($enrollableUnreachablePatient)) {
            event(new AutoEnrollableCollected($enrollableUnreachablePatient));
        }
    }

//    }

    /**
     * @param $practiceId
     * @param $amount
     * @return array
     */
    private function getUnreachablePatients($practiceId)
    {
        $userIds = [];
        Enrollee::whereNotNull('user_id')
            ->where('source', '=', Enrollee::UNREACHABLE_PATIENT)
            ->where('practice_id', $practiceId)
            ->whereDoesntHave('enrollmentInvitationLink')
            ->whereIn('status', [
                Enrollee::QUEUE_AUTO_ENROLLMENT,
            ])
            ->orderBy('id', 'asc')
            ->select('user_id')
            ->each(function ($unreachable) use (&$userIds) {
                $userIds[] = $unreachable->user_id;
            });

        return $userIds;
    }
}
