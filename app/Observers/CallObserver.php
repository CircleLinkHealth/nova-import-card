<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\Call;
use App\Console\Commands\CountPatientMonthlySummaryCalls;
use App\Events\CarePlanWasApproved;
use App\Jobs\MatchCpmCallWithTwilioCallJob;
use App\Note;
use App\Notifications\CallCreated;
use App\Services\ActivityService;
use App\Services\Calls\SchedulerService;
use App\Services\NotificationService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Facades\Notification;

class CallObserver
{
    /**
     * @var ActivityService
     */
    private $activityService;
    /**
     * @var NotificationService
     */
    private $notificationService;

    public function __construct(ActivityService $activityService, NotificationService $notificationService)
    {
        $this->activityService     = $activityService;
        $this->notificationService = $notificationService;
    }

    /**
     * @param $call
     */
    public function createNotification($call)
    {
        if ( ! auth()->check()) {
            return;
        }

        optional($call->outboundUser)->notify(new CallCreated($call, auth()->user()));
    }

    public function saved(Call $call)
    {
        if ($call->isDirty('status')) {
            $patient = User::withTrashed()
                ->ofType('participant')
                ->where('id', $call->inbound_cpm_id)
                ->orWhere('id', $call->outbound_cpm_id)
                ->first();

            if ($patient) {
                if ($this->shouldApproveCarePlan($call)) {
                    $this->approveCarePlan($patient);
                }

                $date = Carbon::parse($call->updated_at);

                $this->activityService->processMonthlyActivityTime($patient->id, $date);

                $this->matchVoiceCallWithCpmCallRecord($call);
            }
        }

        if (Carbon::parse($call->called_date)->isLastMonth()) {
            app(CountPatientMonthlySummaryCalls::class)->countCalls(now()->subMonth()->startOfMonth(), [$call->patientId()]);
        }

        if (Call::REACHED === $call->status || Call::DONE === $call->status) {
            Call::where('id', $call->id)->update(['asap' => false]);
            $call->markAttachmentNotificationAsRead($call->outboundUser);
        }

        if ($call->shouldSendLiveNotification()) {
            $this->createNotification($call);
        }
    }

    private function approveCarePlan(User $patient)
    {
        $note = Note::wherePatientId($patient->id)->whereType(
            SchedulerService::PROVIDER_REQUEST_FOR_CAREPLAN_APPROVAL_TYPE
        )->first();
        if ( ! $note) {
            return;
        }
        event(new CarePlanWasApproved($patient, $note->author));
    }

    private function matchVoiceCallWithCpmCallRecord(Call $call)
    {
        if ( ! $call->called_date) {
            return;
        }

        MatchCpmCallWithTwilioCallJob::dispatch($call);
    }

    private function shouldApproveCarePlan(Call $call)
    {
        return SchedulerService::PROVIDER_REQUEST_FOR_CAREPLAN_APPROVAL_TYPE === $call->sub_type
            && SchedulerService::TASK_TYPE                                   === $call->type
            && $call->isDirty('status')
            && Call::DONE === $call->status;
    }
}
