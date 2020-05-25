<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Jobs;

use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSelfEnrollmentInvitationToUnreachablePatients implements ShouldQueue
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
     * The number of patients we dispatched jobs to send invitations to.
     *
     * @var int
     */
    private $dispatched = 0;
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

    public function handle()
    {
        $this->getUnreachablePatients($this->practiceId)->chunk(100, function ($enrollees) {
            foreach ($enrollees as $enrollee) {
                SendSelfEnrollmentInvitation::dispatch($enrollee->user);

                if (++$this->dispatched === $this->amount) {
                    //break chunking
                    return false;
                }
            }
        });
    }

    private function getUnreachablePatients(int $practiceId)
    {
        return Enrollee::whereNotNull('user_id')
            ->has('user')
            ->with('user')
            ->where('source', '=', Enrollee::UNREACHABLE_PATIENT)
            ->where('practice_id', $practiceId)
            // NOTE: "whereDoesntHave" makes sure we dont invite Unreachable/Non responded - Users (Patients) second time
            ->whereDoesntHave('enrollmentInvitationLinks')
            ->whereIn('status', [
                Enrollee::QUEUE_AUTO_ENROLLMENT,
            ])
            ->orderBy('id', 'asc');
    }
}
