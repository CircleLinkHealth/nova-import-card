<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SmartOnFhirSso\Http\Controllers;

use CircleLinkHealth\SmartOnFhirSso\Events\LoginEvent;
use CircleLinkHealth\SmartOnFhirSso\Http\Requests\MetadataResponse;
use CircleLinkHealth\SmartOnFhirSso\Http\Requests\OAuthResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SsoController extends Controller
{
    const EPIC            = 'epic';
    const SMART_HEALTH_IT = 'smarthealthit';

    public function getAuthTokenFromEpic(Request $request)
    {
        $platform = self::EPIC;
        $response = $this->authenticate($platform, $request->input('code'));
        event(new LoginEvent($platform, $response->encounter, $response->patientFhirId));

        return session()->get('url.intended', route('login'));
    }

    public function getAuthTokenFromSmartHealthIt(Request $request)
    {
        $platform = self::SMART_HEALTH_IT;
        $response = $this->authenticate($platform, $request->input('code'));
        event(new LoginEvent($platform, $response->encounter, $response->patientFhirId));

        return session()->get('url.intended', route('login'));
    }

    public function launch(Request $request)
    {
        $iss      = $request->input('iss');
        $platform = $this->getPlatform($iss);
        if ( ! $platform) {
            throw new \Exception("sso not implemented: $iss");
        }

        $launchToken = $request->input('launch');
        $metadata    = $this->getMetadataEndpoints($platform, $iss);
        $redirectUrl = $this->getAuthorizationUrl($platform, $iss, $launchToken, $metadata);

        return redirect()->to($redirectUrl);
    }

    private function authenticate(string $platform, string $code): OAuthResponse
    {
        $metadataResponse = $this->getMetadataEndpointsFromCache($platform);
        $clientId         = $this->getClientId($platform);
        $redirectUrl      = $this->getRedirectUrl($platform);

        $response = Http::asForm()
            ->post($metadataResponse->tokenUrl, [
                'grant_type'   => 'authorization_code',
                'code'         => $code,
                'redirect_uri' => $redirectUrl,
                'client_id'    => $clientId,
            ]);

        return new OAuthResponse($response->json());
    }

    private function getAuthorizationUrl(string $platform, string $iss, string $launchToken, MetadataResponse $metadataResponse): string
    {
        $clientId    = $this->getClientId($platform);
        $redirectUrl = $this->getRedirectUrl($platform);

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

    private function getClientId(string $platform)
    {
        if (isProductionEnv()) {
            switch ($platform) {
                case 'epic':
                    return config('smartonfhir.epic_app_client_id');
                case 'smarthealthit':
                    return 'anystringisgood';
                default:
                    return null;
            }
        }

        switch ($platform) {
            case 'epic':
                return config('smartonfhir.epic_app_staging_client_id');
            case 'smarthealthit':
                return 'anystringisgood';
            default:
                return null;
        }
    }

    private function getMetadataCacheKey(string $platform): string
    {
        return $this->getClientId($platform).'::metadata';
    }

    private function getMetadataEndpoints(string $platform, string $iss): MetadataResponse
    {
        return Cache::remember($this->getMetadataCacheKey($platform), 30, function () use ($platform, $iss) {
            $response = Http::withHeaders([
                'Accept'         => 'application/fhir+json',
                'Epic-Client-ID' => $this->getClientId($platform),
            ])->get($iss.'/metadata');

            return new MetadataResponse($response->json());
        });
    }

    private function getMetadataEndpointsFromCache(string $platform): MetadataResponse
    {
        return Cache::get($this->getMetadataCacheKey($platform));
    }

    private function getPlatform(string $iss): ?string
    {
        if (Str::contains($iss, 'epic')) {
            return self::EPIC;
        }
        if (Str::contains($iss, 'smarthealthit.org')) {
            return self::SMART_HEALTH_IT;
        }

        return null;
    }

    private function getRedirectUrl(string $platform): ?string
    {
        switch ($platform) {
            case self::EPIC:
                return urlencode(route('smart.on.fhir.sso.epic.code'));
            case self::SMART_HEALTH_IT:
                return route('smart.on.fhir.sso.smarthealthit.code');
            default:
                return null;
        }
    }
}
