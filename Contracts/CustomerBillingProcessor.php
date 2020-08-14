<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Contracts;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Http\Resources\ApprovablePatientCollection;

interface CustomerBillingProcessor
{
    //For ABP
    public function fetchApprovablePatients(int $customerModelId, Carbon $month, $pageSize = 30): ApprovablePatientCollection;

    //attach, and fulfill services whenever pertinent
    public function processServicesForAllPatients(int $customerModelId, Carbon $month): void;

    //any interaction we have with the DB will be through this class
    public function repo(): CustomerBillingProcessorRepository;
}
