<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\SelfEnrollmentEnrollees;
use App\Jobs\SelfEnrollmentUnreachablePatients;
use Illuminate\Console\Command;

class SendEnrollmentNotifications extends Command
{
    const SEND_NOTIFICATIONS_LIMIT_FOR_TESTING = 2;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:sendEnrollmentNotifications';

    /**
     * Create a new command instance.
     *e.
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
     * @throws \ReflectionException
     *
     *
     * @return mixed
     */
    public function handle()
    {
        SelfEnrollmentUnreachablePatients::withChain([
            new SelfEnrollmentEnrollees(),
        ])->dispatch();
    }
}
