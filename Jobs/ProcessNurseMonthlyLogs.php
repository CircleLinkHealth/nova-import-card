<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Jobs;

use CircleLinkHealth\Core\Traits\ScoutMonitoredDispatchable as Dispatchable;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\NurseTimeAlgorithms\AlternativeCareTimePayableCalculator;
use CircleLinkHealth\SharedModels\Entities\Activity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessNurseMonthlyLogs implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;
    /**
     * @var Activity
     */
    protected $activity;

    /**
     * Create a new job instance.
     */
    public function __construct(Activity $activity)
    {
        $this->activity = $activity;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->activity->load('patient');
        $this->activity->load('provider.nurseInfo');

        $nurse = $this->activity->provider->nurseInfo;
        if ( ! is_a($nurse, Nurse::class)) {
            return;
        }

        (new AlternativeCareTimePayableCalculator())
            ->adjustNursePayForActivity($nurse->id, $this->activity);
    }
}
