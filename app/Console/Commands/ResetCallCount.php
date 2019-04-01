<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Customer\Entities\Patient;
use Illuminate\Console\Command;

class ResetCallCount extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'reset all call counts for this patient to 0.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:call_count';

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
        Patient::withTrashed()
            ->update([
                'no_call_attempts_since_last_success' => 0,
            ]);

        $this->info('Call count reset.');
    }
}
