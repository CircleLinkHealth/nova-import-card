<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SmartOnFhirSso\Services;

use CircleLinkHealth\SmartOnFhirSso\Http\Controllers\EpicSsoController;
use CircleLinkHealth\SmartOnFhirSso\Http\Controllers\SmartHealthItSsoController;
use CircleLinkHealth\SmartOnFhirSso\Http\Requests\MetadataResponse;
use CircleLinkHealth\SmartOnFhirSso\Http\Requests\OAuthResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SsoService
{
    public function authenticate(string $platform, string $code): OAuthResponse
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

    public function getClientId(string $platform)
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

    public function getMetadataEndpoints(string $platform, string $iss): MetadataResponse
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

    public function getPlatform(string $iss): ?string
    {
        if (Str::contains($iss, 'epic')) {
            return EpicSsoController::PLATFORM;
        }
        if (Str::contains($iss, 'smarthealthit.org')) {
            return SmartHealthItSsoController::PLATFORM;
        }

        return null;
    }

    public function getRedirectUrl(string $platform): ?string
    {
        switch ($platform) {
            case EpicSsoController::PLATFORM:
                return urlencode(route('smart.on.fhir.sso.epic.code'));
            case SmartHealthItSsoController::PLATFORM:
                return route('smart.on.fhir.sso.smarthealthit.code');
            default:
                return null;
        }
    }
}
