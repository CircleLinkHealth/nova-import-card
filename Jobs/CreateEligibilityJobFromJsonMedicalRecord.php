<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\Eligibility\Adapters\JsonMedicalRecordAdapter;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateEligibilityJobFromJsonMedicalRecord implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * @var EligibilityBatch
     */
    public $batch;
    /**
     * @var string
     */
    public $clhJsonMedicalRecord;

    /**
     * Create a new job instance.
     */
    public function __construct(EligibilityBatch $batch, string $clhJsonMedicalRecord)
    {
        $this->batch                = $batch;
        $this->clhJsonMedicalRecord = $clhJsonMedicalRecord;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $mr = new JsonMedicalRecordAdapter($this->clhJsonMedicalRecord);
        $mr->createEligibilityJob($this->batch);
    }
}
