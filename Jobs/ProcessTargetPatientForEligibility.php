<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\Eligibility\Factories\AthenaEligibilityCheckableFactory;
use CircleLinkHealth\SharedModels\Entities\EligibilityJob;
use CircleLinkHealth\SharedModels\Entities\TargetPatient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessTargetPatientForEligibility implements ShouldQueue, ShouldBeEncrypted
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
     * @var \CircleLinkHealth\SharedModels\Entities\TargetPatient
     */
    protected $targetPatient;

    /**
     * Create a new job instance.
     */
    public function __construct(TargetPatient $targetPatient)
    {
        $this->targetPatient = $targetPatient;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->processEligibility();
        } catch (\Exception $exception) {
            $this->targetPatient->status = TargetPatient::STATUS_ERROR;
            $this->targetPatient->save();

            throw $exception;
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return [
            'athena',
            'targetpatientid:'.$this->targetPatient->id,
            'batchid:'.$this->targetPatient->batch_id,
        ];
    }

    private function processEligibility()
    {
        $this->targetPatient->loadMissing('batch');

        if ( ! $this->targetPatient->batch) {
            throw new \Exception('A batch is necessary to process a target patient.');
        }

        return tap(
            app(AthenaEligibilityCheckableFactory::class)
                ->makeAthenaEligibilityCheckable($this->targetPatient)
                ->createAndProcessEligibilityJobFromMedicalRecord(),
            function (EligibilityJob $eligibilityJob) {
                $this->targetPatient->setStatusFromEligibilityJob($eligibilityJob);
                $this->targetPatient->eligibility_job_id = $eligibilityJob->id;
                $this->targetPatient->save();
            }
        );
    }
}
