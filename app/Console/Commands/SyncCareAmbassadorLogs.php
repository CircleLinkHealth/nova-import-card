<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\SyncCareAmbassadorLogs as SyncCareAmbassadorLogsJob;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Console\Command;

class SyncCareAmbassadorLogs extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync CareAmbassadorLog stats with actual data from enrollees table.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollment:sync-ca-logs {startDate} {endDate?}';

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
        $startDate = Carbon::parse($this->argument('startDate'));

        $endDateInput = $this->argument('endDate');
        $endDate      = $endDateInput ? Carbon::parse($endDateInput) : $startDate;

        $careAmbassadorUsers = User::with('careAmbassador')
            ->has('careAmbassador')
            ->get();

        foreach ($careAmbassadorUsers as $ca) {
            $currentDate = $startDate->copy();

            while ($currentDate->lessThanOrEqualTo($endDate)) {
                SyncCareAmbassadorLogsJob::dispatch($ca, $currentDate);

                $currentDate->addDay();
            }
        }
    }
}
