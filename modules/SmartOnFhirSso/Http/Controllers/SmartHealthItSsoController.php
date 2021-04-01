<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SmartOnFhirSso\Http\Controllers;

use CircleLinkHealth\SmartOnFhirSso\Events\LoginEvent;
use CircleLinkHealth\SmartOnFhirSso\Services\SsoService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SmartHealthItSsoController extends Controller
{
    const PLATFORM = 'smarthealthit';

    private SsoService $service;

    public function __construct()
    {
        $this->service = app(SsoService::class);
    }

    public function getAuthToken(Request $request)
    {
        $response = $this->service->authenticate(self::PLATFORM, $request->input('code'));
        event(new LoginEvent(self::PLATFORM, $response->encounter, $response->patientFhirId));

        return session()->get('url.intended', route('login'));
    }
}
