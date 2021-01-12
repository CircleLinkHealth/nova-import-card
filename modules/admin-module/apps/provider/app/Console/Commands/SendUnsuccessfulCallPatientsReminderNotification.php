<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Notifications\PatientUnsuccessfulCallNotification;
use App\Services\Calls\SchedulerService;
use CircleLinkHealth\Core\Entities\DatabaseNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendUnsuccessfulCallPatientsReminderNotification extends Command
{
    const DAYS_TO_GO_BACK = 2;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder notification to unreached patients';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:unreached-patients-reminder {--testing}';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('testing')) {
            //move forward to the future, so that the command can be tested
            Carbon::setTestNow(now()->addDays(self::DAYS_TO_GO_BACK));
        }

        $userIds = collect();

        // 1. get notifications sent out 2 days ago, make sure we haven't notified today already
        DatabaseNotification::whereType(PatientUnsuccessfulCallNotification::class)
            ->whereBetween('created_at', [now()->subDays(self::DAYS_TO_GO_BACK)->startOfDay(), now()->subDays(self::DAYS_TO_GO_BACK)->endOfDay()])
            ->whereNotIn('notifiable_id', function ($q) {
                $q->select(['notifiable_id'])
                    ->from((new DatabaseNotification())->getTable())
                    ->whereType(PatientUnsuccessfulCallNotification::class)
                    ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()]);
            })
            ->each(function (DatabaseNotification $notification) use ($userIds) {
                // unlikely, but this could happen (most probably in a testing scenario):
                // in one day, 3 unsuccessful calls to same patient,
                // so this notification went out to same patient more than once
                if ($userIds->contains($notification->notifiable_id)) {
                    return;
                }

                // 2. check if we already have callback task since the notification
                $task = SchedulerService::getAsapTaskSince($notification->notifiable_id, SchedulerService::SCHEDULE_NEXT_CALL_PER_PATIENT_SMS, $notification->created_at);
                if ($task) {
                    return;
                }

                // 3. get last unsuccessful call, which has nurse that called
                $call = SchedulerService::getLastUnsuccessfulCall($notification->notifiable_id, $notification->created_at);
                if ($call && $call->inboundUser) {
                    $call->inboundUser->notify(new PatientUnsuccessfulCallNotification($call, true));
                    $userIds->push($call->inbound_cpm_id);
                }
            });

        if ($userIds->isNotEmpty()) {
            $str = implode(',', $userIds->toArray());
            $this->info("Sending reminders to users: $str");
        }

        return 0;
    }
}
