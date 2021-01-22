<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AssignCAUnassignedTimeToCAEnrollees extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The CAs may have time that is not assigned to Enrollees (loading time)';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ca:unassignedTimeToEnrollees';

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
     * @return int
     */
    public function handle()
    {
        $this->info('Starting CA unassigned page timers to Enrollees');

        \CircleLinkHealth\Customer\Entities\User::ofType('care-ambassador')
            ->with(['assignedEnrollees' => function ($e) {
                $e->whereNotNull('last_attempt_at');
            },
                'pageTimersAsProvider' => function ($pt) {
                    $pt->whereNull('enrollee_id')
                        ->where('duration', '!=', 0)
                        ->where('activity_type', '!=', 'CA - No more patients');
                }, ])
            ->chunk(2, function ($caUsers) {
                foreach ($caUsers as $ca) {
                    $enrollees = $ca->assignedEnrollees->all();

                    if (empty($enrollees)) {
                        continue;
                    }

                    $enrolleesCount = count($enrollees);

                    $loadingTimers = $ca->pageTimersAsProvider->all();

                    if (empty($loadingTimers)) {
                        continue;
                    }

                    $loadingTimersCount = count($loadingTimers);

                    $i = 0;
                    $enrolleeIndex = 0;
                    while ($i < $loadingTimersCount) {
                        $timer = $loadingTimers[$i];

                        $enrolleeIndex = $enrolleeIndex < $enrolleesCount ? $enrolleeIndex : $enrolleeIndex - $enrolleesCount;

                        $enrollee = $enrollees[$enrolleeIndex];

                        $timer->enrollee_id = $enrollee->id;
                        $timer->save();

                        ++$i;
                        ++$enrolleeIndex;
                    }
                }
            });

        $this->info('Unassigned time assigned to enrollees.');
    }
}
