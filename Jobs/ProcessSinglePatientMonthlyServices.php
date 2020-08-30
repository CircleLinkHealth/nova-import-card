<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Builders\ApprovablePatientUsersQuery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessSinglePatientMonthlyServices implements ShouldQueue
{
    use ApprovablePatientUsersQuery;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Carbon $month;

    protected int $patientId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $patientId, Carbon $month)
    {
        $this->patientId = $patientId;
        $this->month     = $month;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $patient = $this
            ->approvablePatientUserQuery($this->patientId, $this->month)
            ->with(['patientInfo.location.chargeableMonthlySummaries' => function ($summary) {
                $summary->with(['chargeableService'])
                    ->createdOn($this->month, 'chargeable_month');
            }])
            ->first();

        ProcessPatientMonthlyServices::dispatch(
            $patient,
            $patient->patientInfo->location->availableServiceProcessors($this->month),
            $this->month
        );
    }
}
