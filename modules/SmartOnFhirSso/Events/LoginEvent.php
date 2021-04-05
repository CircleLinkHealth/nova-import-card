<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SmartOnFhirSso\Events;

class LoginEvent
{
    public ?string $ehrPatientId;
    public string $ehrUserId;
    public string $platform;

    public function __construct(string $platform, string $ehrUserId, ?string $ehrPatientId)
    {
        $this->platform     = $platform;
        $this->ehrUserId    = $ehrUserId;
        $this->ehrPatientId = $ehrPatientId;
    }
}
