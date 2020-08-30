<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Console\Commands;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Jobs\MigratePracticeServicesFromChargeablesToLocationSummariesTable as Job;
use Illuminate\Console\Command;

class MigratePracticeServicesFromChargeablesToLocationSummariesTable extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Billing-revamp: Get CS from chargeables for a single Practice, and migrate to chargeable_location_monthly_summaries.';
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'billing:migrate-practice-services {practiceId} {month?}';

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
        /** @var Carbon */
        $month = ! empty($this->argument('month')) ? Carbon::parse($this->argument('month')) : Carbon::now()->startOfMonth();

        if ($month->notEqualTo($month->copy()->startOfMonth())) {
            $month->startOfMonth();
        }

        Job::dispatch($this->argument('practiceId'), $month);
    }
}
