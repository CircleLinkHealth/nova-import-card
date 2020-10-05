<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use MichaelLedin\LaravelJob\Job;

class CompareCurrentAndLegacyBillingData extends Job
{
    protected Carbon $month;

    /**
     * Create a new job instance.
     */
    public function __construct(Carbon $month = null)
    {
        $this->month = $month ?? Carbon::now()->startOfMonth()->startOfDay();
    }

    public static function fromParameters(...$parameters)
    {
        $date = isset($parameters[0]) ? Carbon::parse($parameters[0]) : null;

        return new self($date);
    }

    public function getMonth(): Carbon
    {
        return $this->month;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Practice::activeBillable()
            ->get()
            ->each(fn (Practice $p) => CompareCurrentAndLegacyBillingDataForPractice::dispatch($p->id, $this->getMonth()));
    }
}
