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
use Illuminate\Support\Facades\Log;

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
        $enrollabe = $this->enrollable;

        if ($enrollabe->checkForSurveyOnlyRole()) {
            $enrollabe = Enrollee::findOrFail($this->enrollable->id);
        }

        if ( ! $enrollabe) {
            Log::critical("Cannot find user or enrollee[$enrollabe->id]. Will not send enrollment email.");
        }

        $hasRequestedInfoOnInvitation = $enrollabe->statusRequestsInfo()->exists();

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
