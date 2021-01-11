<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Jobs;

use App\SelfEnrollment\Domain\CreateSurveyOnlyUserFromEnrollee as ActionClass;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
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
