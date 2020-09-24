<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use App\Contracts\HasUniqueIdentifierForDebounce;
use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;
use CircleLinkHealth\Customer\Entities\User;

class ProcessSinglePatientMonthlyServices extends PatientMonthlyBillingProcessingJob implements HasUniqueIdentifierForDebounce
{
    protected string $month;

    protected int $patientId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $patientId, Carbon $month)
    {
        $this->patientId = $patientId;
        $this->month     = ($month ?? Carbon::now()->startOfMonth()->startOfDay())->toDateString();
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
        /** @var User */
        $patient = $this->repo()
            ->patientWithBillingDataForMonth($this->getPatientId(), $this->getMonth())
            ->first();

        if (is_null($patient->patientInfo->location)) {
            sendSlackMessage('#billing_alerts', "Patient ({$patient->id}) does not have location attached. Cannot Process Billing, please investigate");

            return;
        }

        $this->processor()->process(
            (new PatientMonthlyBillingDTO())
                ->subscribe($patient->patientInfo->location->availableServiceProcessors($this->getMonth()))
                ->forPatient($patient->id)
                ->forMonth($this->getMonth())
                ->withProblems(...$patient->patientProblemsForBillingProcessing()->toArray())
        );
    }
}
