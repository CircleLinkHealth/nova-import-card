<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use CircleLinkHealth\CcmBilling\Contracts\PatientMonthlyBillingProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PatientProcessorEloquentRepository;

abstract class PatientMonthlyBillingProcessingJob
{
    protected PatientMonthlyBillingProcessor $processor;

    protected PatientProcessorEloquentRepository $repo;

    public function processor(): PatientMonthlyBillingProcessor
    {
        if ( ! isset($this->processor)) {
            $this->processor = app(PatientMonthlyBillingProcessor::class);
        }

        return $this->processor;
    }

    public function repo(): PatientProcessorEloquentRepository
    {
        if ( ! isset($this->repo)) {
            $this->repo = app(PatientProcessorEloquentRepository::class);
        }

        return $this->repo;
    }
}
