<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Contracts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

interface PatientProcessorEloquentRepository
{
    public function patientWithBillingDataForMonth(int $patientId, Carbon $month): Builder;
}
