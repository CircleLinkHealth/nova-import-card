<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Console;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Jobs\ProcessAllPracticePatientMonthlyServices as Job;
use Illuminate\Console\Command;

class ProcessAllPracticePatientMonthlyServices extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process all practice patients';
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'billing:process-all-practice-patients {month?} {--fulfill}';

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

        Job::dispatch($this->argument('month'), (bool) $this->option('fulfill'));
    }
}
