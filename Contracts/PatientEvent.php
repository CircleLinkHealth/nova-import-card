<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Contracts;

interface PatientEvent
{
    public function getPatientId(): int;
    
    public function shouldDebounce(): bool;
    
    public function debounceDuration(): int;
}
