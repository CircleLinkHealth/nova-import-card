<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\AttachBillableProblemsToSummary;
use App\Repositories\BillablePatientsEloquentRepository;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
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
    protected $signature = 'summaries:attach-problems-to-last-month
                                {date? : the month we are calculating for in YYYY-MM-DD}
                                {practiceIds? : comma separated. leave empty to recalculate for all}
                                {--reset-actor : delete actor id}
                                {--from-scratch : unlock the month and delete actor id, and problems}
                                ';

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
        ini_set('max_execution_time', 600);

        $practiceIds = array_filter(explode(',', $this->argument('practiceIds')));

        $datePassed = $this->argument('date');
        $month      = $datePassed
            ? Carbon::parse($datePassed)->startOfMonth()
            : Carbon::now()->subMonth()->startOfMonth();

        Practice::active()
            ->when(
                $practiceIds,
                function ($q) use ($practiceIds) {
                    $q->whereIn('id', $practiceIds);
                }
            )
            ->chunk(
                1,
                function ($practices) use ($month) {
                    foreach ($practices as $practice) {
                        $this->comment("BEGIN processing $practice->display_name for {$month->toDateString()}");

                        $this->billablePatientsRepo->billablePatients($practice->id, $month)
                            ->chunk(
                                30,
                                function ($users) {
                                    foreach ($users as $user) {
                                        $pms = $user->patientSummaries->first();

                                        if ((bool) $this->option('from-scratch')) {
                                            $pms->reset();
                                            $pms->save();
                                        }

                                        if ((bool) $this->option('reset-actor')) {
                                            $pms->actor_id = null;
                                            $pms->save();
                                        }

                                        AttachBillableProblemsToSummary::dispatch(
                                            $pms
                                        );
                                    }
                                }
                            );

                        $this->output->success(
                            "END processing $practice->display_name for {$month->toDateString()}"
                        );
                    }
                }
            );
    }
}
