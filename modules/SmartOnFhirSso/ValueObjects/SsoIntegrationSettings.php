<?php

namespace CircleLinkHealth\SmartOnFhirSso\ValueObjects;

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

class SsoIntegrationSettings
{
    public string $clientId;
    public string $redirectUrl;

    public function __construct(string $clientId, string $redirectUrl)
    {
        $this->clientId    = $clientId;
        $this->redirectUrl = $redirectUrl;
    }
}
