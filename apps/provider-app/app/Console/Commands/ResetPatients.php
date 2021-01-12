<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ResetPatients extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset patient values (ccm time, call counter, etc) at the beginning of the month.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:patients';

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
        $this->call('reset:ccm_time');

        $this->info('Patients reset.');
    }
}
