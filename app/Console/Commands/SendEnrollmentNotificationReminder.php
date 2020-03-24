<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\SendEnrollmentPatientsReminder;
use App\Jobs\SendEnrollmentUnreachablePatientsReminder;
use Illuminate\Console\Command;

class SendEnrollmentNotificationReminder extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks enrollees who did not respond on first mail and sends reminders to enroll now';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:sendEnrollmentNotificationReminder';

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
     * @return mixed
     */
    public function handle()
    {
//        At this point Enrollees and Unreachable Patients are both User model
//        @todo:Here, after Constantinos implementation will have to: this should change to ENROLLEE
        SendEnrollmentPatientsReminder::dispatch();
    }
}
