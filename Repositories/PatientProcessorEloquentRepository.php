<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Builders\ApprovablePatientUsersQuery;
use CircleLinkHealth\CcmBilling\Contracts\PatientProcessorEloquentRepository as Repository;
use Illuminate\Database\Eloquent\Builder;

class PatientProcessorEloquentRepository implements Repository
{
    use ApprovablePatientUsersQuery;

    public function patientWithBillingDataForMonth(int $patientId, Carbon $month): Builder
    {
        return $this
            ->approvablePatientUserQuery($patientId, $month)
            ->with(['patientInfo.location.chargeableMonthlySummaries' => function ($summary) {
                $summary->with(['chargeableService'])
                    ->createdOn($this->getMonth(), 'chargeable_month');
            }]);
    }
}
