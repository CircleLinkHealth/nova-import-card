<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SetChargeableServiceIdInNurseCareRateLogs extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Read chargeable service id from activity and set it on nurse care rate log';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nursecareratelogs:set-chargeable-service-id {month}';

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
        $count = 0;
        $month = Carbon::parse($this->argument('month'))->startOfMonth();
        NurseCareRateLog::whereHas('activity', function ($q) {
            $q->whereNotNull('chargeable_service_id')
                ->select(['id', 'chargeable_service_id']);
        })
            ->whereNull('chargeable_service_id')
            ->whereBetween('performed_at', [
                $month,
                now()->endOfMonth(),
            ])
            ->chunk(100, function ($items) use (&$count) {
                $items->each(function (NurseCareRateLog $item) {
                    $item->chargeable_service_id = $item->activity->chargeable_service_id;
                    $item->save();
                });
                $count += 100;
                $this->info("$count processed so far.");
            });

        return 0;
    }
}
