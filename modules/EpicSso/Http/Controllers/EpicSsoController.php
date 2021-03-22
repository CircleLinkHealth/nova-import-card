<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\EpicSso\Http\Controllers;

use CircleLinkHealth\EpicSso\Events\EpicSsoLoginEvent;
use CircleLinkHealth\EpicSso\Http\Requests\MetadataResponse;
use CircleLinkHealth\EpicSso\Http\Requests\OAuthResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class EpicSsoController extends Controller
{
    public function code(Request $request)
    {
        // todo: get token from epic, is this needed?
        $response = $this->authenticateInEpic($request->input('code'));
        // todo: authenticate in cpm
        event(new EpicSsoLoginEvent(0));

        return session()->get('url.intended', route('login'));
    }

    public function launch(Request $request)
    {
        $iss         = $request->input('iss');
        $launchToken = $request->input('launch');
        $metadata    = $this->getMetadataEndpoints($iss);

        return $this->getAuthorizationCode($launchToken, $metadata);
    }

    private function authenticateInEpic(string $code): OAuthResponse
    {
        $metadataResponse = $this->getMetadataEndpointsFromCache();
        $clientId         = $this->getClientId();
        $redirectUrl      = urlencode(route('epic.sso.code'));

        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])
            ->post($metadataResponse->tokenUrl, [
                'grant_type'   => 'authorization_code',
                'code'         => $code,
                'redirect_uri' => $redirectUrl,
                'client_id'    => $clientId,
            ]);

        return new OAuthResponse($response->json());
    }

    private function getAuthorizationCode(string $launchToken, MetadataResponse $metadataResponse)
    {
        $clientId    = $this->getClientId();
        $redirectUrl = urlencode(route('epic.sso.code'));

        return Http::post($metadataResponse->authorizeUrl, [
            'response_type' => 'code',
            'client_id'     => $clientId,
            'redirect_uri'  => $redirectUrl,
            'scope'         => 'launch',
            'launch'        => $launchToken,
            'state'         => 'abcde12345',
        ]);
    }

    private function getClientId()
    {
        return isProductionEnv() ? config('epicsso.app_client_id') : config('epicsso.app_staging_client_id');
    }

    private function getMetadataCacheKey(): string
    {
        return $this->getClientId().'::metadata';
    }

    private function getMetadataEndpoints(string $iss): MetadataResponse
    {
        return Cache::remember($this->getMetadataCacheKey(), 30, function () use ($iss) {
            $response = Http::withHeaders([
                'Accept'         => 'application/fhir+json',
                'Epic-Client-ID' => $this->getClientId(),
            ])->get($iss.'/metadata');

            return new MetadataResponse($response->json());
        });
    }

    private function getMetadataEndpointsFromCache(): MetadataResponse
    {
        return Cache::get($this->getMetadataCacheKey());
    }
}
