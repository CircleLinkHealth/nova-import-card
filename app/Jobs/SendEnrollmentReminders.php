<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Events\AutoEnrollableCollected;
use App\Traits\EnrollableManagement;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEnrollmentReminders implements ShouldQueue
{
    use Dispatchable;
    use EnrollableManagement;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var User
     */
    public $enrollable;

    /**
     * Create a new job instance.
     */
    public function __construct(User $enrollable)
    {
        $this->enrollable = $enrollable;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $isSurveyOnly                 = $this->enrollable->checkForSurveyOnlyRole();
        $hasRequestedInfoOnInvitation = $isSurveyOnly
            ? Enrollee::where('user_id', $this->enrollable->id)->firstOrFail()->statusRequestsInfo()->exists()
            : $this->enrollable->statusRequestsInfo()->exists();

        if ( ! $hasRequestedInfoOnInvitation
            || ! $this->hasSurveyCompleted($this->enrollable)) {
            event(new AutoEnrollableCollected([$this->enrollable->id], true));
        }
    }

    /**
     * @return mixed
     */
    private function enrollablePastReminderExists()
    {
        return $this->enrollable->notifications()->where('data->is_reminder', true)->exists();
    }
}
