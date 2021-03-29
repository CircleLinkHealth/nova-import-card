<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\Core\Jobs\ChunksEloquentBuilderJobV2;
use CircleLinkHealth\Eligibility\Adapters\CreatesEligibilityJobFromObject;
use CircleLinkHealth\Eligibility\CcdaImporter\CcdaImporterWrapper;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\PracticePullMedicalRecord;
use CircleLinkHealth\SharedModels\Entities\EligibilityBatch;
use CircleLinkHealth\SharedModels\Entities\PracticePull\Demographics;
use Illuminate\Database\Eloquent\Builder;

class DispatchPracticePullEligibilityBatch extends ChunksEloquentBuilderJobV2
{
    use CreatesEligibilityJobFromObject;

    protected ?EligibilityBatch $batch = null;
    protected int $batchId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $batchId)
    {
        $this->batchId = $batchId;
    }

    public function getBatch(): ?EligibilityBatch
    {
        if (is_null($this->batch)) {
            $this->batch = EligibilityBatch::with('practice')->find($this->batchId);
        }

        return $this->batch;
    }

    public function getBatchId(): int
    {
        return $this->batchId;
    }

    public function getPracticeId(): int
    {
        return $this->getBatch()->practice_id;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        if ( ! $this->getBatch()) {
            return;
        }

        $this->getBuilder()->cursor()->each(function ($demos) {
            $this->dispatchEligibilityJob($demos, $this->getBatch());
        });
    }

    public function query(): Builder
    {
        return Demographics::where('practice_id', $this->getPracticeId())->whereNull('eligibility_job_id');
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
}
