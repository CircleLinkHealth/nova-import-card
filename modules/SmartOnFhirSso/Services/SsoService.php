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
    public function authenticate(string $redirectUrl, string $clientId, string $code): OAuthResponse
    {
        $metadataResponse = $this->getMetadataEndpointsFromCache($clientId);

        $response = Http::asForm()
            ->post($metadataResponse->tokenUrl, [
                'grant_type'   => 'authorization_code',
                'code'         => $code,
                'redirect_uri' => $redirectUrl,
                'client_id'    => $clientId,
            ]);

        return new OAuthResponse($response->json());
    }

    private function getMetadataCacheKey(string $clientId): string
    {
        return $clientId.'::metadata';
    }

    public function getMetadataEndpoints(string $clientId, string $iss): MetadataResponse
    {
        return Cache::remember($this->getMetadataCacheKey($clientId), 30, function () use ($clientId, $iss) {
            $response = Http::withHeaders([
                'Accept'         => 'application/fhir+json',
                'Epic-Client-ID' => $clientId,
            ])->get($iss.'/metadata');

            return new MetadataResponse($response->json());
        });
    }

    private function getMetadataEndpointsFromCache(string $clientId): MetadataResponse
    {
        return Cache::get($this->getMetadataCacheKey($clientId));
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
}
