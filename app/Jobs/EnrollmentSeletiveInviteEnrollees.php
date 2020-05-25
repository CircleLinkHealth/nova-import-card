<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\EnrollmentInvitationsBatch;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EnrollmentSeletiveInviteEnrollees implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var array
     */
    private $userIds;

    /**
     * Create a new job instance.
     */
    public function __construct(array $userIds)
    {
        $this->userIds = $userIds;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $invitationsBatch = EnrollmentInvitationsBatch::create();
        User::whereIn('id', array_filter($this->userIds))->chunk(100, function ($users) use ($invitationsBatch) {
            foreach ($users as $user) {
                SendSelfEnrollmentInvitation::dispatch($user, $invitationsBatch->id);
            }
        });
    }
}
