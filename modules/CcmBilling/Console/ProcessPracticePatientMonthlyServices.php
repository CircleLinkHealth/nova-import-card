<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Console;


use CircleLinkHealth\CcmBilling\Jobs\ProcessPracticePatientMonthlyServices as Job;
use CircleLinkHealth\Core\Traits\TakesDateAsArgument;
use Illuminate\Console\Command;

class ProcessPracticePatientMonthlyServices extends Command
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
    protected $signature = 'billing:process-practice-patient-services {practiceId} {month? : YYYY-MM-DD}';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Job::dispatch($this->argument('practiceId'), $this->getMonthAsCarbon('month'));
    }
}