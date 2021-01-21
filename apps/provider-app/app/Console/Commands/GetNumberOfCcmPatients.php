<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Console\Command;

class GetNumberOfCcmPatients extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Returns the number ccm patients';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:GetNumberOfCcmPatients';

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
        $count = User::whereHas('ccdProblems', function ($q) {
            $q->where('is_monitored', 1)
                ->whereHas('cpmProblem', function ($cpm) {
                    return $cpm->where('is_behavioral', 0);
                });
        })->count();

        $this->info("${count}");
    }
}
