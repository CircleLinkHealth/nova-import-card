<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Console\Commands;

use CircleLinkHealth\CpmAdmin\Repositories\PatientSummaryEloquentRepository;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use Illuminate\Console\Command;

class SyncNumberOfCallsForCurrentMonth extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save the most updated sum of calls and sum of successful calls to all PatientMonthlySummaries of the current month.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patientSummaries:syncCalls';
    /**
     * @var PatientSummaryEloquentRepository
     */
    private $patientSummaryEloquentRepository;

    /**
     * Create a new command instance.
     */
    public function __construct(PatientSummaryEloquentRepository $patientSummaryEloquentRepository)
    {
        parent::__construct();
        $this->patientSummaryEloquentRepository = $patientSummaryEloquentRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        PatientMonthlySummary::whereMonthYear(Carbon::now()->startOfMonth())->chunk(500, function ($summaries) {
            foreach ($summaries as $summary) {
                $this->patientSummaryEloquentRepository->syncCallCounts($summary)->save();
            }
        });
    }
}
