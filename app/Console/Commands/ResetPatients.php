<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ResetPatients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:patients';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset patient values (ccm time, call counter, etc) at the beginning of the month.';

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
        $this->call('reset:ccm_time');
        $this->call('reset:call_count');

        $this->info('Patients reset.');
    }
}
