<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Console;

use CircleLinkHealth\CcmBilling\Jobs\GenerateServiceSummariesForAllPracticeLocations;
use CircleLinkHealth\Core\Traits\TakesDateAsArgument;
use Illuminate\Console\Command;

class GenerateServiceSummariesForAllPracticeLocationsCommand extends Command
{
    use TakesDateAsArgument;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate (renew) ChargeablePatientMonthlySummaries for a month.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:generate-location-summaries {month? : YYYY-MM-DD}';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        GenerateServiceSummariesForAllPracticeLocations::dispatch($this->getMonthAsCarbon('month'));
    }
}
