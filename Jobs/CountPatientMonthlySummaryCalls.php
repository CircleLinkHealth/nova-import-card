<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\SharedModels\Repositories\CallRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CountPatientMonthlySummaryCalls implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private Carbon $month;

    private int $pmsId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $pmsId, Carbon $month)
    {
        $this->pmsId = $pmsId;
        $this->month = $month;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(CallRepository $callRepository)
    {
        $pms = PatientMonthlySummary::findOrFail($this->pmsId);

        $noOfSuccessfulCalls = $callRepository->numberOfSuccessfulCalls(
            $pms->patient_id,
            $this->month
        );

        if ($noOfSuccessfulCalls != $pms->no_of_successful_calls) {
            Log::debug("user_id:{$pms->patient_id}:pms_id:{$pms->id} no_of_successful_calls changing from {$pms->no_of_successful_calls} to ${noOfSuccessfulCalls}");
            $pms->no_of_successful_calls = $noOfSuccessfulCalls;
        }

        $noOfCalls = $callRepository->numberOfCalls($pms->patient_id, $this->month);

        if ($noOfCalls != $pms->no_of_calls) {
            Log::debug("user_id:{$pms->patient_id}:pms_id:{$pms->id} no_of_calls changing from {$pms->no_of_calls} to ${noOfCalls}");
            $pms->no_of_calls = $noOfCalls;
        }

        if ($pms->isDirty()) {
            $pms->save();
        }
    }
}
