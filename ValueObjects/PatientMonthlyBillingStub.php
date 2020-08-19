<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;


use Carbon\Carbon;

class PatientMonthlyBillingStub
{
    protected AvailableServiceProcessors $availableServiceProcessors;
    
    protected Carbon $chargeableMonth;
}