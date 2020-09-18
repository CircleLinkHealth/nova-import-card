<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Algorithms\Invoicing\AlternativeCareTimePayableCalculator;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\SharedModels\Entities\Activity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessNurseMonthlyLogs implements ShouldQueue
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
        $this->activity->load('patient.patientInfo');
        $this->activity->load('provider.nurseInfo');

        $nurse = $this->activity->provider->nurseInfo;

        if ( ! is_a($nurse, Nurse::class)) {
            return;
        }

        (new AlternativeCareTimePayableCalculator($nurse))
            ->adjustNursePayForActivity($this->activity);
    }
}
