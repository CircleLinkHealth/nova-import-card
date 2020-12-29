<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\HasUniqueIdentifierForDebounce;
use CircleLinkHealth\CcmBilling\Domain\Patient\ProcessPatientSummaries;
use CircleLinkHealth\CcmBilling\Facades\BillingCache;
use CircleLinkHealth\Core\Jobs\EncryptedLaravelJob as Job;

class ProcessSinglePatientMonthlyServices extends Job implements HasUniqueIdentifierForDebounce
{
    protected string $month;

    protected int $patientId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $patientId, Carbon $month = null)
    {
        $this->patientId = $patientId;
        $this->month     = ($month ?? Carbon::now()->startOfMonth())->toDateString();
    }

    public static function fromParameters(string ...$parameters)
    {
        $date = isset($parameters[1]) ? Carbon::parse($parameters[1]) : null;

        return new static((int) $parameters[0], $date);
    }

    public function getMonth(): Carbon
    {
        return Carbon::parse($this->month);
    }

    public function getPatientId(): int
    {
        return $this->patientId;
    }

    public function getUniqueIdentifier(): string
    {
        return (string) $this->getPatientId().$this->getMonth()->toDateString();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        BillingCache::clearPatients([$this->getPatientId()]);

        (app(ProcessPatientSummaries::class))->execute($this->getPatientId(), $this->getMonth());
    }

    public function retryUntil(): \DateTime
    {
        return now()->addDay();
    }
}
