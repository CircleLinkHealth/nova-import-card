<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Jobs;

use CircleLinkHealth\SharedModels\Domain\CreateSurveyOnlyUserFromEnrollee as ActionClass;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateSurveyOnlyUserFromEnrollee implements ShouldQueue
{
    //TODO: TEMPORARY PLACEHOLDER, REPLACE WITH SELF-ENROLLMENT CLASS
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
