<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Console;

use CircleLinkHealth\CcmBilling\Jobs\CheckPatientSummariesHaveBeenCreated;
use CircleLinkHealth\Core\Traits\TakesDateAsArgument;
use Illuminate\Console\Command;

class CheckPatientSummariesHaveBeenCreatedCommand extends Command
{
    use TakesDateAsArgument;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check that ChargeablePatientMonthlySummaries have been created for a month.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:check-patient-summaries-created {month? : YYYY-MM-DD}';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        CheckPatientSummariesHaveBeenCreated::dispatch($this->getMonthAsCarbon('month'));
    }
}
