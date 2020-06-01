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
    protected $signature = 'command:sendFirstEnrolleesReminder {limit?} {practiceId?} {--patients} {--enrollees}';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public static function dispatchEnrolleeReminders(?int $practiceId = null, ?int $limit = null)
    {
        RemindEnrollees::dispatch(now()->subDays(2), $practiceId, $limit);
        RemindEnrollees::dispatch(now()->subDays(4), $practiceId, $limit);
    }

    public static function dispatchUnreachablePatientReminders(?int $practiceId = null, ?int $limit = null)
    {
        RemindUnreachablePatients::dispatch(now()->subDays(2), $practiceId, $limit);
        RemindUnreachablePatients::dispatch(now()->subDays(4), $practiceId, $limit);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $limit      = $this->argument('limit') ?? null;
        $practiceId = $this->argument('practiceId') ?? null;

        if ($this->option('enrollees')) {
            self::dispatchEnrolleeReminders($practiceId, $limit);
        }

        if ($this->option('patients')) {
            self::dispatchUnreachablePatientReminders($practiceId, $limit);
        }
    }
}
