<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Algorithms\Invoicing\AlternativeCareTimePayableCalculator;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use Illuminate\Console\Command;

class FixSuccessfulCallsOfCareRateLogs extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix care rate logs';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'care-rate-logs:fix-logs {startDate} {endDate}';

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
        $totalCount    = 0;
        $totalModified = 0;
        $startDateStr  = $this->argument('startDate');
        $startDate     = Carbon::parse($startDateStr);
        $endDateStr    = $this->argument('endDate');
        $endDate       = Carbon::parse($endDateStr);

        NurseCareRateLog::with(['activity'])
            ->whereBetween('created_at', [
                $startDate->copy()->startOfDay()->toDateTimeString(),
                $endDate->copy()->startOfDay()->toDateTimeString(),
            ])
            ->chunk(100, function ($items) use (&$totalCount, &$totalModified) {
                $items->each(function ($item) use (&$totalCount, &$totalModified) {
                    ++$totalCount;
                    if ( ! $item->activity) {
                        return;
                    }
                    if ( ! AlternativeCareTimePayableCalculator::isActivityForSuccessfulCall($item->activity)) {
                        return;
                    }

                    $item->is_successful_call = 1;
                    $item->save();
                    ++$totalModified;
                });
            });

        $this->info("Out of $totalCount nurse_care_rate_logs entries, $totalModified were modified.");
    }
}
