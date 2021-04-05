<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SmartOnFhirSso\Services;

use CircleLinkHealth\SmartOnFhirSso\Http\Controllers\EpicSsoController;
use CircleLinkHealth\SmartOnFhirSso\Http\Controllers\SmartHealthItSsoController;
use CircleLinkHealth\SmartOnFhirSso\Http\Requests\MetadataResponse;
use CircleLinkHealth\SmartOnFhirSso\Http\Requests\OAuthResponse;
use CircleLinkHealth\SmartOnFhirSso\ValueObjects\IdTokenDecoded;
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

    public function decodeIdToken(string $idToken): IdTokenDecoded
    {
        $arr      = collect(explode('.', $idToken));
        $result   = [];
        $result[] = json_decode(base64_decode($arr[0]), true);
        $result[] = json_decode(base64_decode($arr[1]), true);
        $result[] = $arr[2];

        return new IdTokenDecoded($result);
    }

    public function getMetadataEndpoints(string $clientId, string $iss): MetadataResponse
    {
        return $this->getCache()->remember($this->getMetadataCacheKey($clientId), 30, function () use ($clientId, $iss) {
            $response = Http::withHeaders([
                'Accept'         => 'application/fhir+json',
                'Epic-Client-ID' => $clientId,
            ])->get($iss.'/metadata');

            return new MetadataResponse($response->json());
        });
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

    private function getCache()
    {
        $store = app()->environment('local') ? null : 'dynamodb';

        return Cache::store($store);
    }

    private function getMetadataCacheKey(string $clientId): string
    {
        return $clientId.'::metadata';
    }

    private function getMetadataEndpointsFromCache(string $clientId): MetadataResponse
    {
        return $this->getCache()->get($this->getMetadataCacheKey($clientId));
    }
}
