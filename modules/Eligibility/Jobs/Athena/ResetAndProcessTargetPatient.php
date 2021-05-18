<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs\Athena;

use CircleLinkHealth\Eligibility\Jobs\ProcessTargetPatientForEligibility;
use CircleLinkHealth\SharedModels\Entities\TargetPatient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ResetAndProcessTargetPatient implements ShouldQueue, ShouldBeEncrypted
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
    protected ?int          $batchId;
    protected TargetPatient $targetPatient;

    /**
     * Create a new job instance.
     *
     * @param  mixed|null $after
     * @return void
     */
    public function __construct(TargetPatient $targetPatient, ?int $batchId = null)
    {
        $this->targetPatient = $targetPatient;
        $this->batchId       = $batchId;
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return array
     */
    public function backoff()
    {
        return [60, 180, 300];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (null !== $this->batchId) {
            $this->targetPatient->batch_id = $this->batchId;
            $this->targetPatient->status = 'to_process';
            $this->targetPatient->save();
            ProcessTargetPatientForEligibility::dispatch($this->targetPatient->id);
        }
    }
}
