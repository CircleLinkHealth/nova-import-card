<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientMonthlyBillingProcessor;
use CircleLinkHealth\CcmBilling\Processors\Patient\BHI;
use CircleLinkHealth\CcmBilling\Processors\Patient\CCM;
use CircleLinkHealth\CcmBilling\Processors\Patient\MonthlyProcessor;
use CircleLinkHealth\CcmBilling\ValueObjects\AvailableServiceProcessors;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingStub;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPatientMonthlyServices implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected AvailableServiceProcessors $availableServiceProcessors;

    protected Carbon $chargeableMonth;

    protected User $patient;

    protected PatientMonthlyBillingProcessor $processor;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $patient, AvailableServiceProcessors $availableServiceProcessors, Carbon $chargeableMonth)
    {
        //todo: add bool var fulfill
        $this->patient                    = $patient;
        $this->availableServiceProcessors = $availableServiceProcessors;
        $this->chargeableMonth            = $chargeableMonth;
        $this->processor                  = new MonthlyProcessor();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $stub = (new PatientMonthlyBillingStub())
            ->subscribe($this->availableServiceProcessors)
            ->forPatient($this->patient->id)
            ->forMonth($this->chargeableMonth)
            ->withProblems(collect([
                [
                    'code' => ChargeableService::CCM,
                ],
                [
                    'code' => ChargeableService::CCM,
                ],
                [
                    'code' => ChargeableService::BHI,
                ],
            ]));

        $this->processor->process($stub);
    }
}
