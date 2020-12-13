<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Console;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Chargeable;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use Illuminate\Console\Command;

class ResetPMSChargeableServicesForMonth extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove Chargeable Services from Patient Monthly Summaries for a given month. (Currently Unavailable in Production)';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:clear-pms-cs {month?}';

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
        if (app()->environment('production')) {
            $this->warn('Cannot be ran on the production environment.');

            return;
        }

        $monthYear = ! is_null(($month = $this->argument('month') ?? null))
                ? Carbon::parse($month)->startOfMonth()
                : Carbon::now()->startOfMonth();

        $this->info('Deleting PMS CS for month:'.$monthYear->toDateString());
        $this->resetPmsForABP($monthYear);
        $this->info('Deletion completed.');
    }

    private function resetPmsForABP(Carbon $month)
    {
        PatientMonthlySummary::createdOn($month, 'month_year')
            ->update([
                'actor_id' => null,
                'approved' => 0,
                'rejected' => null,
                'needs_qa' => null,
            ]);

        $pmsIds = PatientMonthlySummary::createdOn($month, 'month_year')
            ->pluck('id')
            ->toArray();

        Chargeable::whereIn('chargeable_id', $pmsIds)
            ->where('chargeable_type', PatientMonthlySummary::class)
            ->delete();
    }
}
