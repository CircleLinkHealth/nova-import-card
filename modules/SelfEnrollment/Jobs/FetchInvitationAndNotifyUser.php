<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Jobs;

use AshAllenDesign\ShortURL\Models\ShortURL;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SelfEnrollment\Notifications\NotifySelfEnrollmentUserErrorIsFixed;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchInvitationAndNotifyUser implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::with([
            'enrollee' => function ($enrolle) {
                $enrolle->with('enrollmentInvitationLinks');
                $enrolle->where('status', Enrollee::QUEUE_AUTO_ENROLLMENT);
            },
        ])
            ->where('id', $this->userId)
            ->whereHas('enrollee.enrollmentInvitationLinks')
            ->first();

        if ( ! $user) {
            Log::notice("[FetchInvitationAndNotifyUser] failed to find data for user_id $this->userId");

            return;
        }

        $invitationLink = $user->enrollee->getLastEnrollmentInvitationLink()->url;
        $shortLink      = ShortURL::where('destination_url', $invitationLink)->first();

        if ($shortLink && $shortLink->default_short_url) {
            $invitationLink = $shortLink->default_short_url;
        }

        $user->notify(new NotifySelfEnrollmentUserErrorIsFixed($invitationLink));
    }
}
