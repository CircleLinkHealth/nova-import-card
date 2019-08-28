<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands\Athena;

use App\TargetPatient;
use Illuminate\Console\Command;

class DetermineTargetPatientEligibility extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieves problems and insurances of a given patient from the Athena API';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'athena:DetermineTargetPatientEligibility {batchId? : The Eligibility Batch Id}';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        TargetPatient::where('status', '=', 'to_process')
            ->with('batch')
            ->get()
            ->each(function (TargetPatient $targetPatient) {
                $targetPatient->processEligibility();
            });
    }
}
