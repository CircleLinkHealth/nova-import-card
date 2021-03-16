<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Console;

use CircleLinkHealth\CcmBilling\Jobs\CheckLocationSummariesHaveBeenCreated;
use CircleLinkHealth\Core\Traits\TakesDateAsArgument;
use Illuminate\Console\Command;

class CheckLocationSummariesHaveBeenCreatedCommand extends Command
{
    use TakesDateAsArgument;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check that ChargeableLocationMonthlySummaries have been created for a month.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:check-location-summaries-created {month? : YYYY-MM-DD}';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        CheckLocationSummariesHaveBeenCreated::dispatch($this->getMonthAsCarbon('month'));
    }
}
