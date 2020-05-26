<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\SelfEnrollment\Domain\RemindEnrollees;
use App\SelfEnrollment\Domain\RemindUnreachablePatients;
use Illuminate\Console\Command;

class SendFirstSelfEnrollmentReminder extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send first reminder to unresponsive enrollees 2 days after initial invitation';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:sendFirstEnrolleesReminder {--patients} {--enrollees}';

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
        if ($this->option('enrollees')) {
            RemindEnrollees::createForInvitesSentTwoDaysAgo()->dispatchToQueue();
        }

        if ($this->option('patients')) {
            RemindUnreachablePatients::createForInvitesSentTwoDaysAgo()->dispatchToQueue();
        }
    }
}
