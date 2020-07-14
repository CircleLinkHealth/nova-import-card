<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

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
        // todo
        return 0;
    }
}
