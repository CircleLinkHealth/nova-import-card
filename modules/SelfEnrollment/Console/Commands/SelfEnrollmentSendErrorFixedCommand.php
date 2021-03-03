<?php

namespace CircleLinkHealth\SelfEnrollment\Console\Commands;

use CircleLinkHealth\SelfEnrollment\Jobs\FetchInvitationAndNotifyUser;
use Illuminate\Console\Command;

class SelfEnrollmentSendErrorFixedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:self-enrollment-patients-error-fixed {userIds*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sms/Email to Self Enrollment Users that the error they have experienced it is now fixed and they can proceed';

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
        $userIds = $this->argument('userIds');

        foreach ($userIds as $userId){
            FetchInvitationAndNotifyUser::dispatch(intval($userId));
        }

    }
}
