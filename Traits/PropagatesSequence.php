<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Traits;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientChargeableServiceProcessor;

trait PropagatesSequence
{
    protected function attachNext(int $patientId, Carbon $chargeableMonth)
    {
        if (method_exists($this, 'next')) {
            $processor = $this->next();

            $processor->attach($patientId, $chargeableMonth);
        }
    }
    
    abstract public function next(): PatientChargeableServiceProcessor;
}
