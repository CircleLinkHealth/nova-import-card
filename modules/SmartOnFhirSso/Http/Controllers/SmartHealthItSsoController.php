<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SmartOnFhirSso\Http\Controllers;

use CircleLinkHealth\SmartOnFhirSso\Events\LoginEvent;
use CircleLinkHealth\SmartOnFhirSso\Services\SsoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SmartHealthItSsoController extends Controller implements SmartOnFhirSsoController
{
    const PLATFORM = 'smarthealthit';

    private SsoService $service;

    public function __construct()
    {
        $this->service = app(SsoService::class);
    }

    public function getAuthToken(Request $request): RedirectResponse
    {
        $response = $this->service->authenticate($this->getRedirectUrl(), $this->getClientId(), $request->input('code'));
        $decoded  = $this->service->decodeIdToken($response->openIdToken);

        event(new LoginEvent(self::PLATFORM, $decoded->fhirUser, $response->patientFhirId));

        return redirect()->to(session()->get('url.intended', route('login')));
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
}
