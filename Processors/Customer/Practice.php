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
        return BillablePatientUsersQuery::query($monthYear)
            ->ofPractice($this->practiceIds);
    }

    public function patientBillableServicesQuery(Carbon $monthYear): Builder
    {
        return BillableMonthlyChargeableServicesQuery::query($monthYear)
            ->whereHas('patient', fn ($q) => $q->ofPractice($this->practiceIds));
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
