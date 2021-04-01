<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SmartOnFhirSso\Http\Controllers;

use CircleLinkHealth\SmartOnFhirSso\Events\LoginEvent;
use CircleLinkHealth\SmartOnFhirSso\Services\SsoService;
use Exception;
use Illuminate\Auth\AuthenticationException;
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
        try {
            event(new LoginEvent(self::PLATFORM, $decoded->sub, $response->patientFhirId));
        }
        catch (AuthenticationException $e) {
            return redirect(route('smart.on.fhir.sso.not.auth'));
        }
        catch (Exception $e) {
            session()->put('error_message', $e->getMessage());
            return redirect(route('smart.on.fhir.sso.error'));
        }

        return redirect()->to(session()->get('url.intended', route('login')));
    }

    public function showNotAuth() {
        return view('smartonfhirsso::not-auth');
    }

    public function showError() {
        return view('smartonfhirsso::error');
    }

    public function getClientId(): string
    {
        if (isProductionEnv()) {
            return config('smartonfhirsso.epic_app_client_id');
        }

        return config('smartonfhirsso.epic_app_staging_client_id');
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
