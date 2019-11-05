<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\Call;
use App\Notifications\CallCreated;
use App\Services\ActivityService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Facades\Notification;

class CallObserver
{
    /**
     * @var ActivityService
     */
    private $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    public function createSendPusherNotification($call)
    {
        $notify = $call->outboundUser;
        Notification::send($notify, new CallCreated($call, auth()->user()));
    }

    public function saved(Call $call)
    {
        if ($call->isDirty('status')) {
            $patient = User::ofType('participant')
                ->where('id', $call->inbound_cpm_id)
                ->orWhere('id', $call->outbound_cpm_id)
                ->first();

            $date = Carbon::parse($call->updated_at);

            $this->activityService->processMonthlyActivityTime($patient->id, $date);

            $start = $date->copy()->startOfMonth();
            $end   = $date->copy()->endOfMonth();

            $no_of_calls = Call::where(function ($q) {
                $q->whereNull('type')
                    ->orWhere('type', '=', 'call')
                    ->orWhere('sub_type', '=', 'Call Back');
            })
                ->where(function ($q) use ($patient) {
                    $q->where('outbound_cpm_id', $patient->id)
                        ->orWhere('inbound_cpm_id', $patient->id);
                })
                ->where('called_date', '>=', $start)
                ->where('called_date', '<=', $end)
                ->whereIn('status', ['reached', 'not reached'])
                ->get();

            $no_of_successful_calls = $no_of_calls->where('status', 'reached')->count();

            $summary = PatientMonthlySummary::where('patient_id', $patient->id)
                ->where('month_year', $date->startOfMonth())
                ->update([
                    'no_of_calls'            => $no_of_calls->count(),
                    'no_of_successful_calls' => $no_of_successful_calls,
                ]);
        }
        //Create and send notification to pusher only if if its marked as 'ASAP'.
        if (true === $call->asap) {
            $this->createSendPusherNotification($call);
        }
    }
}
