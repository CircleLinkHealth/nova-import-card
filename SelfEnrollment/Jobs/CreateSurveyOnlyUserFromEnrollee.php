<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\SelfEnrollment\Jobs;

use CircleLinkHealth\Core\Traits\ScoutMonitoredDispatchable as Dispatchable;
use CircleLinkHealth\Eligibility\SelfEnrollment\Domain\CreateSurveyOnlyUserFromEnrollee as ActionClass;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateSurveyOnlyUserFromEnrollee implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private Enrollee $enrollee;

    /**
     * CreateSurveyOnlyUserFromEnrollee constructor.
     */
    public function __construct(Enrollee $enrollee)
    {
        $this->enrollee = $enrollee;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ActionClass::execute($this->enrollee);
    }
}
