<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Traits;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessor;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;

trait IsPartOfSequence
{
    public function attachNext(int $patientId, Carbon $chargeableMonth): ?ChargeablePatientMonthlySummary
    {
        if (method_exists($this, 'next')) {
            if ( ! $this->next() instanceof PatientServiceProcessor) {
                return null;
            }

            if ( ! $this->shouldAttachNext($patientId, $chargeableMonth)) {
                return null;
            }

            return $this->next()
                ->attach($patientId, $chargeableMonth);
        }
    }

    abstract public function next(): ?PatientServiceProcessor;

    abstract public function previous(): ?PatientServiceProcessor;

    protected function shouldAttachNext(int $patientId, Carbon $chargeableMonth): bool
    {
        if ( ! method_exists($this, 'next')) {
            return false;
        }

        if ( ! $this->next() instanceof PatientServiceProcessor) {
            return false;
        }

        return $this->repo()
            ->isChargeableServiceEnabledForLocationForMonth($patientId, $this->next()->code(), $chargeableMonth);
    }
}
