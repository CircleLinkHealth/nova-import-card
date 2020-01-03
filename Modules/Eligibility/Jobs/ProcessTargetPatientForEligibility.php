<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use App\TargetPatient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessTargetPatientForEligibility implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * @var TargetPatient
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
            $this->targetPatient->processEligibility();
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
        ];
    }
}
