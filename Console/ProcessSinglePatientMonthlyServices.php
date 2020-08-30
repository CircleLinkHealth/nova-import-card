<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Console;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Jobs\ProcessSinglePatientMonthlyServices as Job;
use Illuminate\Console\Command;

class ProcessSinglePatientMonthlyServices extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process single patient chargeable services for a given month.';
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'billing:process-single-patient-services {patientId} {month?}';

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

        Job::dispatch($this->argument('patientId'), $month);
    }
}
