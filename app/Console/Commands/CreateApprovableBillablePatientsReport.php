<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\ProcessBillablePatients;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Console\Command;

class CreateApprovableBillablePatientsReport extends Command
{
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
    protected $signature = 'generate:abp
                                {date? : the month we are calculating for in YYYY-MM-DD}
                                {practiceIds? : comma separated. leave empty to recalculate for all}
                                {--reset-actor : delete actor id}
                                {--from-scratch : unlock the month and delete actor id, and problems}
                                {--auto-attest : auto attest conditions where necessary, depending on patient service codes}
                                ';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $practiceIds = array_filter(explode(',', $this->argument('practiceIds')));

        $datePassed = $this->argument('date');
        $month      = $datePassed
            ? Carbon::createFromFormat('Y-m-d', $datePassed)->startOfMonth()
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
                        $this->comment("BEGIN CreateApprovableBillablePatientsReport for $practice->display_name for {$month->toDateString()}");

                        ProcessBillablePatients::dispatch(
                            $practice->id,
                            $month,
                            (bool) $this->option('from-scratch'),
                            (bool) $this->option('reset-actor'),
                            (bool) $this->option('auto-attest')
                        );

                        $this->output->success(
                            "END CreateApprovableBillablePatientsReport for $practice->display_name for {$month->toDateString()}"
                        );
                    }
                }
            );
    }
}
