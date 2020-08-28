<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Console;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Jobs\GenerateServiceSummariesForAllPracticeLocations as Job;
use Illuminate\Console\Command;

class GenerateServiceSummariesForAllPracticeLocations extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate CS summaries for all practice locations based on previous month.';
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'billing:generate-locations-summaries-for-month {month}';

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
        Job::dispatch($month);
    }
}
