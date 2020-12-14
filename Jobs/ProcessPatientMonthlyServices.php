<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Domain\Patient\ProcessPatientSummaries;
use CircleLinkHealth\CcmBilling\Facades\BillingCache;
use CircleLinkHealth\CcmBilling\ValueObjects\AvailableServiceProcessors;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\RateLimitedMiddleware\RateLimited;

class ProcessPatientMonthlyServices implements ShouldQueue
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
        BillingCache::clearPatients([$this->patient->getPatientId()]);

        (app(ProcessPatientSummaries::class))->fromDTO($this->patient);
    }

    public function middleware()
    {
        if (isUnitTestingEnv()) {
            return [];
        }

        $rateLimitedMiddleware = (new RateLimited())
            ->allow(50)
            ->everySeconds(60)
            ->releaseAfterSeconds(20);

        return [$rateLimitedMiddleware];
    }

    public function retryUntil(): \DateTime
    {
        return now()->addDay();
    }
}
