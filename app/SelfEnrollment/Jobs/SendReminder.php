<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Jobs;

use App\SelfEnrollment\Helpers;
use CircleLinkHealth\Core\Entities\DatabaseNotification;
use CircleLinkHealth\Customer\EnrollableRequestInfo\EnrollableRequestInfo;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendReminder implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var User
     */
    public $patient;

    /**
     * Create a new job instance.
     */
    public function __construct(User $patient)
    {
        $this->patient = $patient;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ( ! $this->shouldRun()) {
            return;
        }

        $invitation = $this->patient->enrollee->enrollmentInvitationLinks->first();

        SendInvitation::dispatch($this->patient, optional($invitation)->button_color, true);
    }

    public function shouldRun(): bool
    {
        if (Helpers::hasCompletedSelfEnrollmentSurvey($this->patient)) {
            return false;
        }

        $this->patient->loadMissing(['enrollee.statusRequestsInfo', 'enrollee.enrollmentInvitationLinks']);

        if (empty($this->patient->enrollee)) {
            throw new \Exception("user[{$this->patient->id}] does not have an enrollee.");
        }

        if ($this->patient->enrollee->statusRequestsInfo instanceof EnrollableRequestInfo) {
            return false;
        }

        if ($this->patientHasReminderNotification()) {
            return false;
        }

        return true;
    }

    private function patientHasReminderNotification(): bool
    {
        return DatabaseNotification::whereIn('notifiable_type', [\App\User::class, User::class])->where('notifiable_id', $this->patient->id)->where('data->is_reminder', true)->selfEnrollmentInvites()->exists();
    }
}
