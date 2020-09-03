<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Builders\ApprovablePatientServicesQuery;
use CircleLinkHealth\CcmBilling\Builders\ApprovablePatientUsersQuery;
use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessor;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\CcmBilling\ValueObjects\AvailableServiceProcessors;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Location;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class LocationProcessorEloquentRepository implements LocationProcessorRepository
{
    use ApprovablePatientServicesQuery;
    use ApprovablePatientUsersQuery;

    public function availableLocationServiceProcessors(int $locationId, Carbon $chargeableMonth): AvailableServiceProcessors
    {
        return AvailableServiceProcessors::push($this->getProcessorsFromLocationServiceCodes($locationId, $chargeableMonth));
    }

    public function paginatePatients(int $locationId, Carbon $chargeableMonth, int $pageSize): LengthAwarePaginator
    {
        return $this->patientsQuery($locationId, $chargeableMonth)->paginate($pageSize);
    }

    public function patients(int $locationId, Carbon $monthYear): Collection
    {
        return $this->patientsQuery($locationId, $monthYear)->get();
    }

    public function patientServices(int $locationId, Carbon $monthYear): Builder
    {
        return $this->approvablePatientServicesQuery($monthYear)
            ->whereHas('patient.patientInfo', fn ($q) => $q->where('preferred_contact_location', $locationId));
    }

    public function patientsQuery(int $locationId, Carbon $monthYear): Builder
    {
        return $this->approvablePatientUsersQuery($monthYear)
            ->whereHas('patientInfo', fn ($q) => $q->where('preferred_contact_location', $locationId));
    }

    public function servicesForMonth($locationId, Carbon $chargeableMonth): Builder
    {
        //todo: add query traits for location
        return ChargeableLocationMonthlySummary::with(['chargeableService' => function ($cs) {
            $cs->select('code');
        }])
            ->where('location_id', $locationId)
            ->createdOn($chargeableMonth, 'chargeable_month');
    }

    public function store(int $locationId, string $chargeableServiceCode, Carbon $month, float $amount = null): ChargeableLocationMonthlySummary
    {
        return ChargeableLocationMonthlySummary::updateOrCreate(
            [
                'location_id'           => $locationId,
                'chargeable_service_id' => ChargeableService::getChargeableServiceIdUsingCode($chargeableServiceCode),
                'chargeable_month'      => $month,
            ],
            [
                'amount' => $amount,
            ]
        );
    }

    private function getProcessorsFromLocationServiceCodes(int $locationId, Carbon $chargeableMonth): array
    {
        return $this->servicesForMonth($locationId, $chargeableMonth)
            ->get()
            ->map([$this, 'getProcessorUsingCode']);
    }

    private function getProcessorUsingCode(ChargeableLocationMonthlySummary $clms): PatientServiceProcessor
    {
        return $clms->chargeableService->processor();
    }
    
    /**
     * @param int $locationId
     * @param Carbon|null $month
     * @return Builder|Builder[]|Collection|Model
     */
    public function locationWithPracticeLocationsWithSummaries(int $locationId, ?Carbon $month = null)
    {
        //todo: add trait query
        return Location::with([
            'practice.locations' => function ($location) use ($month) {
            //todo: add scope
                $location->with([
                    'chargeableServiceSummaries' => function ($summary) use ($month) {
                        $summary->with(['chargeableService'])
                                ->when(! is_null($month), function($q) use ($month){
                                     $q->createdOn($month, 'chargeable_month');
                                });
                            }
                            ])
                ->when(! is_null($month), function($q) use ($month){
                    $q->whereHas('chargeableServiceSummaries', function ($summary) {
                        $summary->createdOn($this->month, 'chargeable_month');
                    });
                });
        }])
            ->find($locationId);
    }
    
}
