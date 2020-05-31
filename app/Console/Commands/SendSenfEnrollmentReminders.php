<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\SelfEnrollment\Domain\RemindEnrollees;
use App\SelfEnrollment\Domain\RemindUnreachablePatients;
use Illuminate\Console\Command;

class SendSenfEnrollmentReminders extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders to unreachables 2 and 4 days after initial invitation';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:sendFirstEnrolleesReminder {limit?} {--patients} {--enrollees}';

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
        $job = null;

        if ($this->option('enrollees')) {
            $job = RemindEnrollees::createForInvitesSentTwoDaysAgo();
        }

        if ($this->option('patients')) {
            $job = RemindUnreachablePatients::createForInvitesSentTwoDaysAgo();
        }

        if (is_null($job)) {
            return;
        }

        with($job, function ($job) {
            if (is_numeric($limit = $this->argument('limit'))) {
                $job->setLimit((int) $limit);
            }

            $job->dispatchToQueue();
        });
    }
}
