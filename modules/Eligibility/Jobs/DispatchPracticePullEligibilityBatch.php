<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\Eligibility\Adapters\CreatesEligibilityJobFromObject;
use CircleLinkHealth\Eligibility\CcdaImporter\CcdaImporterWrapper;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\PracticePullMedicalRecord;
use CircleLinkHealth\SharedModels\Entities\EligibilityBatch;
use CircleLinkHealth\SharedModels\Entities\PracticePull\Demographics;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchPracticePullEligibilityBatch implements ShouldQueue, ShouldBeEncrypted
{
    use CreatesEligibilityJobFromObject;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected int $batchId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $batchId)
    {
        $this->batchId = $batchId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $batch = EligibilityBatch::with('practice')->find($this->batchId);

        if ( ! $batch) {
            return;
        }

        $practiceId = $batch->practice_id;

        $this->query($practiceId)->cursor()->each(function ($demos) use ($batch) {
            $this->dispatchEligibilityJob($demos, $batch);
        });
    }

    protected function dispatchEligibilityJob(Demographics $demos, EligibilityBatch $batch)
    {
        if ( ! $demos->billing_provider_user_id && $demos->referring_provider_name) {
            $provider = CcdaImporterWrapper::mysqlMatchProvider($demos->referring_provider_name, $demos->practice_id);
            if ($provider) {
                $demos->billing_provider_user_id = $provider->id;
                $demos->save();
            }
        }

        $ej = $this->createFromBlueButtonObject((new PracticePullMedicalRecord($demos->mrn, $demos->practice_id))->toObject(), $batch, $batch->practice);

        if ( ! $demos->eligibility_job_id) {
            $demos->eligibility_job_id = $ej->id;
            $demos->save();
        }

        ProcessSinglePatientEligibility::dispatch($ej->id);

        return $ej;
    }

    private function query(int $practiceId)
    {
        return Demographics::where('practice_id', $practiceId)->whereNull('eligibility_job_id');
    }
}
