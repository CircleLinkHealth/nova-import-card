<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SmartOnFhirSso\Http\Controllers;

use CircleLinkHealth\SmartOnFhirSso\Http\Requests\MetadataResponse;
use CircleLinkHealth\SmartOnFhirSso\Services\SsoService;
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

        $launchToken = $request->input('launch');
        $metadata    = $this->service->getMetadataEndpoints($platform, $iss);
        $redirectUrl = $this->getAuthorizationUrl($platform, $iss, $launchToken, $metadata);

        return redirect()->to($redirectUrl);
    }

    private function getAuthorizationUrl(string $platform, string $iss, string $launchToken, MetadataResponse $metadataResponse): string
    {
        $clientId    = $this->service->getClientId($platform);
        $redirectUrl = $this->service->getRedirectUrl($platform);

        $url         = $metadataResponse->authorizeUrl;
        $queryParams = [
            'aud'           => $iss,
            'response_type' => 'code',
            'client_id'     => $clientId,
            'redirect_uri'  => $redirectUrl,
            'scope'         => 'launch openid fhirUser',
            'launch'        => $launchToken,
            'state'         => 'abcde12345',
        ];
        foreach ($queryParams as $key => $value) {
            $qs[$key] = sprintf('%s=%s', $key, urlencode($value));
        }

        return sprintf('%s?%s', $url, implode('&', $qs));
    }
}
