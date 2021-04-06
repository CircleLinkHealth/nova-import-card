<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SmartOnFhirSso\ValueObjects;

// This file is part of CarePlan Manager by CircleLink Health.

class SsoIntegrationSettings
{
    public string $clientId;
    public string $platform;
    public string $redirectUrl;
    public string $userIdPropertyName;

    public function __construct(string $platform, string $userIdPropertyName, string $clientId, string $redirectUrl)
    {
        $this->platform           = $platform;
        $this->userIdPropertyName = $userIdPropertyName;
        $this->clientId           = $clientId;
        $this->redirectUrl        = $redirectUrl;
    }
}
