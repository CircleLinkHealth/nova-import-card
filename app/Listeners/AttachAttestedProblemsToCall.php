<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\Events\CallIsReadyForAttestedProblemsAttachment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AttachAttestedProblemsToCall implements ShouldQueue
{
    use InteractsWithQueue;
    
    /**
     * The time (seconds) before the job should be processed.
     *
     * @var int
     */
    public $delay = 10;
    
    /**
     * Handle a job failure.
     *
     * @param CallIsReadyForAttestedProblemsAttachment $event
     * @param \Exception $exception
     *
     * @return void
     */
    public function failed(CallIsReadyForAttestedProblemsAttachment $event, $exception)
    {
        $call = $event->getCall();

        \Log::channel('logdna')->info('Failed to attach attested conditions to call/summary.', [
            'patient_id'           => $call->inbound_cpm_id,
            'call_id'              => $call->id,
            'attested_problem_ids' => $event->getProblems(),
            'exception_message'    => $exception->getMessage(),
        ]);
    }
    
    /**
     * Handle the event.
     *
     * @param CallIsReadyForAttestedProblemsAttachment $event
     *
     * @return void
     */
    public function handle(CallIsReadyForAttestedProblemsAttachment $event)
    {
        $call             = $event->getCall();
        $attestedProblems = $event->getProblems();

        $call->attachAttestedProblems($attestedProblems);
    }
}
