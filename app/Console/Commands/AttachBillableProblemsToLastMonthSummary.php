<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\AttachBillableProblemsToSummary;
use App\Practice;
use App\Repositories\BillablePatientsEloquentRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AttachBillableProblemsToLastMonthSummary extends Command
{
    protected $billablePatientsRepo;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Attach 2 billable problems to each of last month\'s summaries';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'summaries:attach-problems-to-last-month';

    /**
     * Create a new command instance.
     */
    public function __construct(BillablePatientsEloquentRepository $billablePatientsRepo)
    {
        parent::__construct();
        $this->billablePatientsRepo = $billablePatientsRepo;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $month = Carbon::now()
            ->subMonth();

        Practice::active()
            ->get()
            ->map(function ($practice) use ($month) {
                $this->billablePatientsRepo->billablePatients($practice->id, $month)
                        ->get()
                        ->map(function ($u) {
                            AttachBillableProblemsToSummary::dispatch($u->patientSummaries->first());
                        });
            });
    }
}
