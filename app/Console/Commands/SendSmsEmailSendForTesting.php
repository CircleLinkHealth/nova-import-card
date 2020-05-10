<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\SelfEnrollmentEnrollees;
use App\Jobs\SelfEnrollmentUnreachablePatients;
use Illuminate\Console\Command;

class SendSmsEmailSendForTesting extends Command
{
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
    protected $signature = 'command:sex';

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
        SelfEnrollmentEnrollees::dispatch(
            null,
            'blue',
            intval(2),
            intval(8)
        );

        SelfEnrollmentUnreachablePatients::dispatch(
            intval(2),
            intval(8)
        );
    }
}
