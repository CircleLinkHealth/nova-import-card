<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\SelfEnrollment\Jobs;

use App\EnrollmentInvitationsBatch;
use CircleLinkHealth\Eligibility\SelfEnrollment\Http\Controllers\SelfEnrollmentController;
use CircleLinkHealth\Eligibility\SelfEnrollment\Helpers;
use CircleLinkHealth\Eligibility\SelfEnrollment\Jobs\SendInvitation;
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
    private $batch;

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

        $invitation = $this->patient->enrollee->enrollmentInvitationLinks->sortByDesc('id')->first();
        $color      = optional($invitation)->button_color ?? SelfEnrollmentController::DEFAULT_BUTTON_COLOR;
        SendInvitation::dispatch($this->patient, $this->getBatch($this->patient->program_id, $color)->id, $color, true);
    }

    public function shouldRun(): bool
    {
        if (Helpers::hasCompletedSelfEnrollmentSurvey($this->patient)) {
            return false;
        }

        $this->patient->loadMissing(['enrollee.enrollableInfoRequest', 'enrollee.enrollmentInvitationLinks']);

        if (empty($this->patient->enrollee)) {
            throw new \Exception("user[{$this->patient->id}] does not have an enrollee.");
        }

        if ($this->patient->enrollee->enrollableInfoRequest instanceof EnrollableRequestInfo) {
            return false;
        }

        if ($this->patientHasReminderNotifications()) {
            return false;
        }

        return true;
    }

    private function getBatch(int $practiceId, string $color): EnrollmentInvitationsBatch
    {
        if (is_null($this->batch)) {
            $this->batch = EnrollmentInvitationsBatch::firstOrCreateAndRemember($practiceId, now()->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.$color);
        }

        return $this->batch;
    }

    private function patientHasReminderNotifications(): bool
    {
        return DatabaseNotification::whereIn('notifiable_type', [\App\User::class, User::class])->where('notifiable_id', $this->patient->id)->where('data->is_reminder', true)->count() >= 2;
    }
}
