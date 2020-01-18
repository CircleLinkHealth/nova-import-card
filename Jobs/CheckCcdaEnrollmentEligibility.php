<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use App\Jobs\EligibilityChecker;
use App\Jobs\EligibilityJob;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckCcdaEnrollmentEligibility implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    protected $ccda;
    protected $practice;
    protected $transformer;
    /**
     * @var EligibilityBatch
     */
    private $batch;
    private $filterInsurance;
    private $filterLastEncounter;
    private $filterProblems;

    /**
     * Create a new job instance.
     *
     * @param $ccda
     */
    public function __construct(
        $ccda,
        Practice $practice,
        EligibilityBatch $batch
    ) {
        if (is_a($ccda, Ccda::class)) {
            $ccda = $ccda->id;
        }

        $this->ccda     = Ccda::find($ccda);
        $this->practice = $practice;
        $this->batch    = $batch;
    }

    /**
     * Execute the job.
     *
     * @throws \Exception
     */
    public function handle()
    {
        if (Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY != $this->ccda->status) {
            return null;
        }

        $ej = $this->ccda->createEligibilityJobFromMedicalRecord();

        if (is_null($ej)) {
            return false;
        }

        return $this->determineEligibility($ej);
    }

    /**
     * @throws \Exception
     *
     * @return EligibilityChecker
     */
    private function determineEligibility(EligibilityJob $job)
    {
        $check = (new EligibilityChecker(
            $job,
            $this->practice,
            $this->batch,
            $this->filterLastEncounter,
            $this->filterInsurance,
            $this->filterProblems,
            true
        ));

        if ($check->getEligibilityJob()->isEligible()) {
            $this->ccda->status = Ccda::ELIGIBLE;
        } else {
            $this->ccda->status = Ccda::INELIGIBLE;
        }

        $this->ccda->save();

        if ($check->getEnrollee()) {
            $check->getEnrollee()->medical_record_type = Ccda::class;
            $check->getEnrollee()->medical_record_id   = $this->ccda->id;
            $check->getEnrollee()->save();
        }

        //to update updated_at
        $this->batch->save();

        return $check;
    }
}
