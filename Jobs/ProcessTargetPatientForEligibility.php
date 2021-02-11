<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\Eligibility\Exceptions\CcdaWasNotFetchedFromAthenaApi;
use CircleLinkHealth\Eligibility\Factories\AthenaEligibilityCheckableFactory;
use CircleLinkHealth\SharedModels\Entities\EligibilityJob;
use CircleLinkHealth\SharedModels\Entities\TargetPatient;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessTargetPatientForEligibility implements ShouldQueue, ShouldBeEncrypted
{
    protected int $targetPatientId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $targetPatientId)
    {
        $this->targetPatientId = $targetPatientId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $tP = TargetPatient::findOrFail($this->targetPatientId);
            $this->processEligibility($tP);
        } catch (\Exception $e) {
            $tP->status      = TargetPatient::STATUS_ERROR;
            $tP->description = $e->getMessage();
            $tP->save();

            throw $e;
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
            'targetpatientid:'.$this->targetPatientId,
        ];
    }

    private function processEligibility(TargetPatient &$tP)
    {
        $tP->loadMissing('batch');

        if ( ! $tP->batch) {
            throw new \Exception('A batch is necessary to process a target patient.');
        }

        try {
            return tap(
                app(AthenaEligibilityCheckableFactory::class)
                    ->makeAthenaEligibilityCheckable($tP)
                    ->createAndProcessEligibilityJobFromMedicalRecord(),
                function (EligibilityJob $eligibilityJob) use ($tP) {
                    $tP->setStatusFromEligibilityJob($eligibilityJob);
                    $tP->eligibility_job_id = $eligibilityJob->id;
                    $tP->save();
                }
            );
        } catch (CcdaWasNotFetchedFromAthenaApi $e) {
            $tP->setStatusFromException($e);
            $tP->save();
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
