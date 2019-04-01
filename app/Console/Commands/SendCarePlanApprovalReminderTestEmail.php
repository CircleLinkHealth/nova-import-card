<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Console\Command;

class SendCarePlanApprovalReminderTestEmail extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test CarePlan approval reminder email to the given User.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:emailCPApprovalReminderTest {userId}';

    /**
     * Create a new command instance.
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
        $tester = $this->argument('userId');

        $user = User::findOrFail($tester);

        $user->sendCarePlanApprovalReminder(10, true);

        $this->output->success('Email sent!');
    }
}
