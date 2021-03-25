<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use CircleLinkHealth\CcmBilling\ValueObjects\AvailableServiceProcessors;
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

    public function getDtoFromPatient(User $patient)
    {
        return PatientMonthlyBillingDTO::generateFromUser($patient, $this->getChargeableMonth());
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->getBuilder()->get()->each(function (User $patient) {
            measureTime("ProcessPatientMonthlyServices:$patient->id", function () use ($patient) {
                ProcessPatientMonthlyServices::dispatch(
                    $this->getDtoFromPatient($patient)
                );
            });
        });
    }

    public function repo(): LocationProcessorRepository
    {
        return app(LocationProcessorRepository::class);
    }
}
