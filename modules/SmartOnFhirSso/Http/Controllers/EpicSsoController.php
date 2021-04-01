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

class EpicSsoController extends Controller implements SmartOnFhirSsoController
{
    const PLATFORM = 'epic';

    private SsoService $service;

    public function __construct()
    {
        $this->service = app(SsoService::class);
    }

    public function getAuthToken(Request $request): RedirectResponse
    {
        $response = $this->service->authenticate($this->getRedirectUrl(), $this->getClientId(), $request->input('code'));
        $decoded  = $this->service->decodeIdToken($response->openIdToken);
        event(new LoginEvent(self::PLATFORM, $decoded->sub, $response->patientFhirId));

        return redirect()->to(session()->get('url.intended', route('login')));
    }

    public function getClientId(): string
    {
        if (isProductionEnv()) {
            return config('smartonfhir.epic_app_client_id');
        }

        return config('smartonfhir.epic_app_staging_client_id');
    }

    public function getPlatform(): string
    {
        return self::PLATFORM;
    }

    public function getRedirectUrl(): string
    {
        return route('smart.on.fhir.sso.epic.code');
    }
}
