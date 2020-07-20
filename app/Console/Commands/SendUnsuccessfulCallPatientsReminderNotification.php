<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Notifications\PatientUnsuccessfulCallNotification;
use App\Services\Calls\SchedulerService;
use CircleLinkHealth\Core\Entities\DatabaseNotification;
use Illuminate\Console\Command;

class SendUnsuccessfulCallPatientsReminderNotification extends Command
{
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
    protected $signature = 'send:unreached-patients-reminder';

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
        //1. get notifications sent out 2 days ago, make sure we haven't notified today already
        $query = DatabaseNotification::whereType(PatientUnsuccessfulCallNotification::class)
            ->whereBetween('created_at', [now()->subDays(2)->startOfDay(), now()->subDays(2)->endOfDay()])
            ->whereNotIn('notifiable_id', function ($q) {
                $q->select(['notifiable_id'])
                    ->from((new DatabaseNotification())->getTable())
                    ->whereType(PatientUnsuccessfulCallNotification::class)
                    ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()]);
            });
        $query->each(function (DatabaseNotification $notification) {
            //2. check if we already have scheduled callback task
            $task = SchedulerService::getNextScheduledActivity($notification->notifiable_id, SchedulerService::SCHEDULE_NEXT_CALL_PER_PATIENT_SMS);
            if ($task) {
                return;
            }

            //3. get last unsuccessful call, which has nurse that called
            $call = SchedulerService::getLastUnsuccessfulCall($notification->notifiable_id, $notification->created_at);
            if ($call) {
                $call->inboundUser->notify(new PatientUnsuccessfulCallNotification($call, true));
            }
        });

        return 0;
    }
}
