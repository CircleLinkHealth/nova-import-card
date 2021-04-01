<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SmartOnFhirSso\Http\Controllers;

use CircleLinkHealth\SmartOnFhirSso\Http\Requests\MetadataResponse;
use CircleLinkHealth\SmartOnFhirSso\Services\SsoService;
use CircleLinkHealth\SmartOnFhirSso\ValueObjects\SsoIntegrationSettings;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SsoController extends Controller
{
    private SsoService $service;

    public function __construct()
    {
        $this->service = app(SsoService::class);
    }

    public function launch(Request $request)
    {
        $iss      = $request->input('iss');
        $platform = $this->service->getPlatform($iss);
        if ( ! $platform) {
            throw new \Exception("sso not implemented: $iss");
        }

        $launchToken      = $request->input('launch');
        $ssoSettings      = $this->getSettings($platform);
        $metadata         = $this->service->getMetadataEndpoints($ssoSettings->clientId, $iss);
        $authorizationUrl = $this->getAuthorizationUrl($ssoSettings, $iss, $launchToken, $metadata);

        return redirect()->to($authorizationUrl);
    }

    private function getAuthorizationUrl(SsoIntegrationSettings $settings, string $iss, string $launchToken, MetadataResponse $metadataResponse): string
    {
        $url         = $metadataResponse->authorizeUrl;
        $queryParams = [
            'aud'           => $iss,
            'response_type' => 'code',
            'client_id'     => $settings->clientId,
            'redirect_uri'  => $settings->redirectUrl,
            'scope'         => 'launch openid fhirUser',
            'launch'        => $launchToken,
            'state'         => 'abcde12345',
        ];
        foreach ($queryParams as $key => $value) {
            $qs[$key] = sprintf('%s=%s', $key, urlencode($value));
        }

        return sprintf('%s?%s', $url, implode('&', $qs));
    }

    private function getSettings(string $platform): ?SsoIntegrationSettings
    {
        /** @var SmartOnFhirSsoController $controller */
        $controller = null;
        switch ($platform) {
            case EpicSsoController::PLATFORM:
                $controller = app(EpicSsoController::class);
                break;
            case SmartHealthItSsoController::PLATFORM:
                $controller = app(SmartHealthItSsoController::class);
                break;
        }
        if ( ! $controller) {
            return null;
        }

        return new SsoIntegrationSettings($controller->getClientId(), $controller->getRedirectUrl());
    }
}
