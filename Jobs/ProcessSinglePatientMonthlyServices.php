<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use App\Contracts\HasUniqueIdentifierForDebounce;
use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientMonthlyBillingProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PatientProcessorEloquentRepository;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessSinglePatientMonthlyServices implements ShouldQueue, HasUniqueIdentifierForDebounce
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Carbon $month;

    protected int $patientId;

    protected PatientMonthlyBillingProcessor $processor;

    protected PatientProcessorEloquentRepository $repo;

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

    public function getMonth(): Carbon
    {
        return $this->month;
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

        $this->processor()->process(
            (new PatientMonthlyBillingDTO())
                ->subscribe($patient->patientInfo->location->availableServiceProcessors($this->getMonth()))
                ->forPatient($patient->id)
                ->forMonth($this->getMonth())
                ->withProblems($patient->patientProblemsForBillingProcessing()->toArray())
        );
    }

    public function processor(): PatientMonthlyBillingProcessor
    {
        if ( ! isset($this->processor)) {
            $this->processor = app(PatientMonthlyBillingProcessor::class);
        }

        return $this->processor;
    }

    public function repo(): PatientProcessorEloquentRepository
    {
        if ( ! isset($this->repo)) {
            $this->repo = app(PatientProcessorEloquentRepository::class);
        }

        return $this->repo;
    }
}
