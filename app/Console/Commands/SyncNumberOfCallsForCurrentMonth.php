<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\PatientMonthlySummary;
use App\Repositories\PatientSummaryEloquentRepository;
use Carbon\Carbon;
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
     *
     * @param PatientSummaryEloquentRepository $patientSummaryEloquentRepository
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
                $this->patientSummaryEloquentRepository->syncCallCounts($summary);
            }
        });
    }
}
