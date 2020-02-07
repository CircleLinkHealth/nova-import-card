<?php

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\Eligibility\Adapters\JsonMedicalRecordAdapter;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateEligibilityJobFromJsonMedicalRecord implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
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
     *
     * @param EligibilityBatch $batch
     * @param string $clhJsonMedicalRecord
     */
    public function __construct(EligibilityBatch $batch, string $clhJsonMedicalRecord)
    {
        $this->batch = $batch;
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
