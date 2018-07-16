<?php

namespace App\Console\Commands;

use App\Patient;
use Illuminate\Console\Command;

class ResetCallCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:call_count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'reset all call counts for this patient to 0.';

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
        Patient::withTrashed()
               ->update([
                   'no_call_attempts_since_last_success' => 0,
               ]);

        $this->info('Call count reset.');
    }
}
