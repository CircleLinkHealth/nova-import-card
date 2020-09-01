<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Entities;

use CircleLinkHealth\Core\Entities\SqlViewModel;
use CircleLinkHealth\TimeTracking\Traits\DateScopesTrait;

class ChargeablePatientMonthlySummaryView extends SqlViewModel
{
    use DateScopesTrait;
    
    protected $table = 'chargeable_patient_monthly_summaries_view';
}
