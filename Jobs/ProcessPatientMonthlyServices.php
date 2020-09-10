<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientMonthlyBillingProcessor;
use CircleLinkHealth\CcmBilling\ValueObjects\AvailableServiceProcessors;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;
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

    protected PatientMonthlyBillingDTO $patient;

    protected PatientMonthlyBillingProcessor $processor;
    
    /**
     * Create a new job instance.
     * @param PatientMonthlyBillingDTO $patient
     */
    public function __construct(PatientMonthlyBillingDTO $patient)
    {
        $this->patient = $patient;
    }

    public function getAvailableServiceProcessors(): AvailableServiceProcessors
    {
        return $this->patient->getAvailableServiceProcessors();
    }

    public function getChargeableMonth(): Carbon
    {
        return $this->patient->getChargeableMonth();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->processor()->process($this->patient);
    }

    public function processor(): PatientMonthlyBillingProcessor
    {
        if ( ! isset($this->processor)) {
            $this->processor = app(PatientMonthlyBillingProcessor::class);
        }

        return $this->processor;
    }
}
