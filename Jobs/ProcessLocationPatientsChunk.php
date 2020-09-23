<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use App\Contracts\ChunksEloquentBuilder;
use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use CircleLinkHealth\CcmBilling\ValueObjects\AvailableServiceProcessors;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessLocationPatientsChunk extends ChunksEloquentBuilderJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected AvailableServiceProcessors $availableServiceProcessors;

    protected int $locationId;

    protected Carbon $month;

    /**
     * Create a new job instance.
     */
    public function __construct(int $locationId, AvailableServiceProcessors $availableServiceProcessors, Carbon $month)
    {
        $this->locationId                 = $locationId;
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
            ->patientsQuery($this->locationId, $this->month, Patient::ENROLLED)
            ->offset($this->getOffset())
            ->limit($this->getLimit());
    }

    public function getChargeableMonth()
    {
        return $this->month;
    }

    public function getLocationId(): int
    {
        return $this->locationId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->getBuilder()->each(function (User $patient) {
            ProcessPatientMonthlyServices::dispatch(
                (new PatientMonthlyBillingDTO())
                    ->subscribe($this->getAvailableServiceProcessors())
                    ->forPatient($patient->id)
                    ->forMonth($this->getChargeableMonth())
                    ->withProblems(...$patient->patientProblemsForBillingProcessing()->toArray())
            );
        });
    }

    public function repo(): LocationProcessorRepository
    {
        return app(LocationProcessorRepository::class);
    }
}
