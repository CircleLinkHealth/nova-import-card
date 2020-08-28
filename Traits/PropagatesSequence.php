<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Traits;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientChargeableServiceProcessor;

trait PropagatesSequence
{
    public function attachNext(int $patientId, Carbon $chargeableMonth)
    {
        if (method_exists($this, 'next')) {
            if ($this->shouldAttachNext($patientId, $chargeableMonth)) {
                $this->next()
                    ->attach($patientId, $chargeableMonth);
            }
        }
    }

    abstract public function next(): PatientChargeableServiceProcessor;

    protected function shouldAttachNext(int $patientId, Carbon $chargeableMonth): bool
    {
        if ( ! method_exists($this, 'next')) {
            return false;
        }

        return $this->repo()
            ->isChargeableServiceEnabledForLocationForMonth($patientId, $this->next()->code(), $chargeableMonth);
    }
}
