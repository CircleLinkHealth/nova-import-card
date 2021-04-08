<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Domain\Patient\ProcessPatientSummaries;
use CircleLinkHealth\CcmBilling\ValueObjects\AvailableServiceProcessors;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPatientMonthlyServices implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected PatientMonthlyBillingDTO $patient;

    /**
     * Create a new job instance.
     */
    public function __construct(PatientMonthlyBillingDTO $patient)
    {
        $this->patient = $patient;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        (app(ProcessPatientSummaries::class))->fromDTO($this->patient);
    }

    /**
     * Added this for the tests
     *
     * @return Carbon
     */
    public function getChargeableMonth(): Carbon {
        return $this->patient->getChargeableMonth();
    }

    /**
     * Added this for the tests
     *
     * @return AvailableServiceProcessors
     */
    public function getAvailableServiceProcessors(): AvailableServiceProcessors {
        return $this->patient->getAvailableServiceProcessors();
    }
}
