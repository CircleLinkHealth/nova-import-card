<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Builders\ApprovablePatientServicesQuery;
use CircleLinkHealth\CcmBilling\Builders\ApprovablePatientUsersQuery;
use CircleLinkHealth\CcmBilling\Contracts\PracticeProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\PatientMonthlyBillingStatus;
use CircleLinkHealth\CcmBilling\Jobs\SetLegacyPmsClosedMonthStatus;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PracticeProcessorEloquentRepository implements PracticeProcessorRepository
{
    use ApprovablePatientServicesQuery;
    use ApprovablePatientUsersQuery;

    public function closeMonth(int $actorId, int $practiceId, Carbon $month)
    {
        $updated = PatientMonthlyBillingStatus::whereHas(
            'patientUser',
            fn ($q) => $q->ofPractice($practiceId)
        )->where('chargeable_month', $month)
            ->update([
                'actor_id' => $actorId,
            ]);

        ChargeableLocationMonthlySummary::whereHas(
            'location',
            fn ($q) => $q->where('practice_id', '=', $practiceId)
        )->where('chargeable_month', '=', $month)
            ->update([
                'is_locked' => true,
            ]);

        SetLegacyPmsClosedMonthStatus::dispatch($practiceId, $month)
            ->onQueue(getCpmQueueName(CpmConstants::HIGH_QUEUE));

        return $updated;
    }

    public function paginatePatients(int $customerModelId, Carbon $chargeableMonth, int $pageSize): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->patientsQuery($customerModelId, $chargeableMonth)
            ->paginate($pageSize);
    }

    public function patients(int $customerModelId, Carbon $monthYear): Collection
    {
        return $this->patientsQuery($customerModelId, $monthYear)->get();
    }

    public function patientServices(int $practiceId, Carbon $monthYear): Builder
    {
        return $this->approvablePatientServicesQuery($monthYear)
            ->whereHas('patient', fn ($q) => $q->ofPractice($practiceId));
    }

    public function patientsQuery(int $customerModelId, Carbon $monthYear): Builder
    {
        return $this->approvablePatientUsersQuery($monthYear)
            ->ofPractice($customerModelId);
    }

    public function practiceWithLocationsWithSummaries(int $practiceId, ?Carbon $month = null): Builder
    {
        //todo: to deprecate - was initially added for auto-assign CS to newly created Location which is an idea we're probably scrapping
    }
}
