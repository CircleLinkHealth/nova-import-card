<?php

namespace App\Console\Commands;

use App\CarePlan;
use App\Mail\CarePlanApprovalReminder;
use App\User;
use Illuminate\Console\Command;
use Mail;

class SendCarePlanApprovalReminderTestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:emailCPApprovalReminderTest {userId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test CarePlan approval reminder email to the given User.';

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
        $tester = $this->argument('userId');

        $user = User::findOrFail($tester);

        $user->sendCarePlanApprovalReminderEmail(true);

        $this->output->success('Email sent!');
    }
}
