<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SmartOnFhirSso\Http\Controllers;

use CircleLinkHealth\SmartOnFhirSso\Services\SsoService;
use CircleLinkHealth\SmartOnFhirSso\ValueObjects\SsoIntegrationSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SmartHealthItSsoController extends Controller implements SmartOnFhirSsoController
{
    const PLATFORM              = 'smarthealthit';
    const USER_ID_PROPERTY_NAME = 'fhirUser';

    private SsoService $service;

    public function __construct()
    {
        $this->service = app(SsoService::class);
    }

    public function getAuthToken(Request $request): RedirectResponse
    {
        $options = new SsoIntegrationSettings(self::PLATFORM, self::USER_ID_PROPERTY_NAME, $this->getRedirectUrl(), $this->getClientId());

        return $this->service->authenticate($options, $request->input('code'));
    }

    public function getClientId(): string
    {
        return 'anystringisgood';
    }

    public function getPlatform(): string
    {
        return self::PLATFORM;
    }

    public function getRedirectUrl(): string
    {
        return route('smart.on.fhir.sso.smarthealthit.code');
    }

    public function getUserIdPropertyName(): string
    {
        return self::USER_ID_PROPERTY_NAME;
    }
}
