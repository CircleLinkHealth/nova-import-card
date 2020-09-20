<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAllPracticePatientMonthlyServices implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Carbon $month;

    /**
     * Create a new job instance.
     */
    public function __construct(Carbon $month = null)
    {
        $this->month = $month ?? Carbon::now()->startOfMonth()->startOfDay();
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
        Practice::get()
            ->each(fn (Practice $p) => ProcessPracticePatientMonthlyServices::dispatch($p->id, $this->getMonth()));
    }
}
