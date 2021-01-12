<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Contracts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

interface PracticeProcessorRepository
{
    public function patientServices(int $practiceId, Carbon $month): Builder;
}
