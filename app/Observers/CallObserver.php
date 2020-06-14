<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\Call;
use App\Events\CarePlanWasApproved;
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
    public function createNotificationAndSendToPusher($call)
    {
        //could be called from a job, or from command line
        if ( ! auth()->check()) {
            return;
        }

        $notify = $call->outboundUser;
        Notification::send($notify, new CallCreated($call, auth()->user()));
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
            }
        }

        if ('reached' === $call->status || 'done' === $call->status) {
            Call::where('id', $call->id)->update(['asap' => false]);
            $call->markAttachmentNotificationAsRead($call->outboundUser);
        }

        //If sub_type = "addendum_response" means it has already been created by AddendumObserver
        //@todo:come up with a better solution for this
        if ($call->shouldSendLiveNotification()) {
            $this->createNotificationAndSendToPusher($call);
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

    private function shouldApproveCarePlan(Call $call)
    {
        return SchedulerService::PROVIDER_REQUEST_FOR_CAREPLAN_APPROVAL_TYPE === $call->sub_type
               && 'task'                                                     === $call->type
               && $call->isDirty('status')
               && 'done' === $call->status;
    }
}
