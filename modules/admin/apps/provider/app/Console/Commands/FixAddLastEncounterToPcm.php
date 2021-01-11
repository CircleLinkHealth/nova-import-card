<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Jobs\AddLastEncounterToEligibilityCheckFromTargetPatient;
use Illuminate\Console\Command;

class FixAddLastEncounterToPcm extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'FixAddLastEncounterToPcm';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:FixAddLastEncounterToPcm';

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
        EligibilityJob::whereJsonLength(
            'data->chargeable_services_codes_and_problems->G2065',
            '>',
            0
        )->select('id')->chunkById(1000, function ($eJs) {
            foreach ($eJs as $e) {
                AddLastEncounterToEligibilityCheckFromTargetPatient::dispatch($e->id);
            }
        });
    }
}
