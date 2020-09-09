<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use App\Contracts\HasUniqueIdentifierForDebounce;
use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Builders\ApprovablePatientUsersQuery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessSinglePatientMonthlyServices implements ShouldQueue, HasUniqueIdentifierForDebounce
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
            ->approvablePatientUserQuery($this->getPatientId(), $this->getMonth())
            ->with(['patientInfo.location.chargeableMonthlySummaries' => function ($summary) {
                $summary->with(['chargeableService'])
                    ->createdOn($this->getMonth(), 'chargeable_month');
            }])
            ->first();

        ProcessPatientMonthlyServices::dispatch(
            $patient,
            $patient->patientInfo->location->availableServiceProcessors($this->month),
            $this->month
        );
    }
    
    public function getUniqueIdentifier(): string
    {
        return (string)$this->getPatientId().$this->getMonth()->toDateString();
    }
    
    public function getPatientId():int{
        return $this->patientId;
    }
    
    public function getMonth(): Carbon
    {
        return $this->month;
    }
}
