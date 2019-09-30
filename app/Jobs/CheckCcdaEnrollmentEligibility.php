<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\EligibilityBatch;
use App\Models\MedicalRecords\Ccda;
use App\Services\EligibilityCheck;
use CircleLinkHealth\Customer\Entities\Practice;
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
     * @param Practice         $practice
     * @param EligibilityBatch $batch
     */
    public function __construct(
        $ccda,
        Practice $practice,
        EligibilityBatch $batch
    ) {
        if (is_a($ccda, Ccda::class)) {
            $ccda = $ccda->id;
        }

        $this->ccda                = Ccda::find($ccda);
        $this->practice            = $practice;
        $this->filterInsurance     = $batch->shouldFilterInsurance();
        $this->filterLastEncounter = $batch->shouldFilterLastEncounter();
        $this->filterProblems      = $batch->shouldFilterProblems();
        $this->batch               = $batch;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        if (Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY != $this->ccda->status) {
            return null;
        }

        return $this->determineEligibility();
    }

    /**
     * @throws \Exception
     *
     * @return EligibilityCheck
     */
    private function determineEligibility()
    {
        $job = $this->ccda->createEligibilityJobFromMedicalRecord();

        $check = (new EligibilityCheck(
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

        return $check;
    }
}
