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
        $query = DatabaseNotification::whereType(PatientUnsuccessfulCallNotification::class)
            ->whereBetween('created_at', [now()->subDays(2)->startOfDay(), now()->subDays(2)->endOfDay()])
            ->whereNotIn('notifiable_id', function ($q) {
                $q->select(['notifiable_id'])
                    ->from((new DatabaseNotification())->getTable())
                    ->whereType(PatientUnsuccessfulCallNotification::class)
                    ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()]);
            });
        $query->each(function (DatabaseNotification $notification) {
            $call = SchedulerService::getLastUnsuccessfulCall($notification->notifiable_id, $notification->created_at);
            if ($call) {
                $call->inboundUser->notify(new PatientUnsuccessfulCallNotification($call, true));
            }
        });

        return 0;
    }
}
