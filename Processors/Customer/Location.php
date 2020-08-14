<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Customer;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\CustomerBillingProcesor;
use Illuminate\Database\Eloquent\Builder;

class Location implements CustomerBillingProcesor
{
    private array $locationsIds = [];

    public function attachServicesToPatients(Carbon $month)
    {
        // TODO: Implement attachServicesToPatients() method.
    }

    public function billablePatientsQuery(Carbon $monthYear): Builder
    {
        return BillablePatientUsersQuery::query($monthYear)
            ->whereHas('patientInfo', fn ($q) => $q->whereIn('preferred_contact_location', $this->locationsIds));
    }

    public function patientBillableServicesQuery(Carbon $monthYear): Builder
    {
        // TODO: Implement patientBillableServicesQuery() method.
    }

    public function setLocationsIds(array $locationsIds): Location
    {
        $this->locationsIds = $locationsIds;

        return $this;
    }
}
