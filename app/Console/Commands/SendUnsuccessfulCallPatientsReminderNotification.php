<?php

namespace App\Console\Commands;

use App\Notifications\PatientUnsuccessfulCallNotification;
use CircleLinkHealth\Core\Entities\DatabaseNotification;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Console\Command;

class SendUnsuccessfulCallPatientsReminderNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:unreached-patients-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder notification to unreached patients';

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
        // todo
        return 0;
    }
}
