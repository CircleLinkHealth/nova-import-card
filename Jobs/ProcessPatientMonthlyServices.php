<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientMonthlyBillingProcessor;
use CircleLinkHealth\CcmBilling\Processors\Customer\Location;
use CircleLinkHealth\CcmBilling\Processors\Patient\MonthlyProcessor;
use CircleLinkHealth\CcmBilling\ValueObjects\AvailableServiceProcessors;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingStub;
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
            ->subscribe($this->getAvailableServiceProcessors())
            ->forPatient($this->patient->id)
            ->forMonth($this->getChargeableMonth())
            ->withProblems($this->patient->patientProblemsForBillingProcessing()->toArray());

        $this->processor->process($stub);
    }
    
    public function getChargeableMonth() : Carbon
    {
        return $this->chargeableMonth;
    }
    
    public function getAvailableServiceProcessors() : AvailableServiceProcessors
    {
        return $this->availableServiceProcessors;
    }
    
    public function getProcessor() : Location
    {
        return $this->processor;
    }
}
