<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use CircleLinkHealth\CcmBilling\Domain\Patient\PatientProblemsForBillingProcessing;
use CircleLinkHealth\CcmBilling\Entities\PatientMonthlyBillingStatus;
use CircleLinkHealth\CcmBilling\ValueObjects\AvailableServiceProcessors;
use CircleLinkHealth\CcmBilling\ValueObjects\ForcedPatientChargeableServicesForProcessing;
use CircleLinkHealth\CcmBilling\ValueObjects\LocationChargeableServicesForProcessing;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientChargeableServicesForProcessing;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessLocationPatientsChunk extends ChunksEloquentBuilderJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected AvailableServiceProcessors $availableServiceProcessors;

    protected array $locationIds;

    protected Carbon $month;

    /**
     * Create a new job instance.
     */
    public function __construct(array $locationIds, AvailableServiceProcessors $availableServiceProcessors, Carbon $month)
    {
        $this->locationIds                = $locationIds;
        $this->availableServiceProcessors = $availableServiceProcessors;
        $this->month                      = $month;
    }

    public function getAvailableServiceProcessors()
    {
        return $this->availableServiceProcessors;
    }

    public function getBuilder(): Builder
    {
        return $this->repo()
            ->patientsQuery($this->locationIds, $this->month, Patient::ENROLLED)
            ->offset($this->getOffset())
            ->limit($this->getLimit());
    }

    public function getChargeableMonth()
    {
        return $this->month;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->getBuilder()->get()->each(function (User $patient) {
            //todo: remove processors and just get from user, call generateFromUser static on DTO
            measureTime("ProcessPatientMonthlyServices:$patient->id", function () use ($patient) {
                ProcessPatientMonthlyServices::dispatch(
                    (new PatientMonthlyBillingDTO())
                        ->subscribe($this->getAvailableServiceProcessors())
                        ->forPatient($patient->id)
                        ->ofLocation($patient->patientInfo->preferred_contact_location)
                        ->setBillingStatusIsTouched(
                            ! is_null(optional($patient->monthlyBillingStatus
                                ->filter(fn (PatientMonthlyBillingStatus $mbs) => $mbs->chargeable_month->equalTo($this->getChargeableMonth()))
                                ->first())->actor_id)
                        )
                        ->forMonth($this->getChargeableMonth())
                        ->withLocationServices(
                            ...LocationChargeableServicesForProcessing::fromCollection($patient->patientInfo->location->chargeableServiceSummaries)
                        )
                        ->withPatientServices(
                            ...PatientChargeableServicesForProcessing::fromCollection($patient)
                        )
                        ->withForcedPatientServices(
                            ...ForcedPatientChargeableServicesForProcessing::fromCollection($patient->forcedChargeableServices)
                        )
                        ->withProblems(...PatientProblemsForBillingProcessing::getArrayFromPatient($patient))
                );
            });
        });
    }

    public function repo(): LocationProcessorRepository
    {
        return app(LocationProcessorRepository::class);
    }
}
