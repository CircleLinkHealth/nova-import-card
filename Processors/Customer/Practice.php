<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Customer;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\CustomerBillingProcesor;
use Illuminate\Database\Eloquent\Builder;

class Practice implements CustomerBillingProcesor
{
    private $practiceIds;

    public function attachServicesToPatients(Carbon $month)
    {
        // TODO: Implement attachServicesToPatients() method.
    }

    public function billablePatientsQuery(Carbon $monthYear): Builder
    {
        return BillablePatientUsersQuery::query($monthYear)->whereHas('practices', fn ($q) => $q->whereIn('id', $this->practiceIds));
    }

    public function patientBillableServicesQuery(Carbon $monthYear): Builder
    {
        return (new Location())
            ->setLocationsIds(\CircleLinkHealth\Customer\Entities\Location::whereIn('practice_id', $this->practiceIds)->pluck('id')->all())
            ->patientBillableServicesQuery($monthYear);
    }

    /**
     * @param  mixed    $practiceIds
     * @return Practice
     */
    public function setPracticeIds(array $practiceIds)
    {
        $this->practiceIds = $practiceIds;

        return $this;
    }
}
