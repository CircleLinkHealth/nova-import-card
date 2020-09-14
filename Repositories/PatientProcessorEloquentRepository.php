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
            ->with(['patientInfo.location.chargeableServiceSummaries' => function ($summary) use ($month) {
                $summary->with(['chargeableService'])
                    ->createdOn($month, 'chargeable_month');
            }]);
    }
}
