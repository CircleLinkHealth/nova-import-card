<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\EpicSso\Http\Controllers;

use CircleLinkHealth\EpicSso\Events\EpicSsoLoginEvent;
use CircleLinkHealth\EpicSso\Http\Requests\MetadataResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class EpicSsoController extends Controller
{
    public function launch(Request $request)
    {
        if ($request->has('launch')) {
            $iss         = $request->input('iss');
            $launchToken = $request->input('launch');
            $metadata    = $this->getMetadataEndpoint($iss);

            return $this->getAuthorizationCode($launchToken, $metadata);
        }
        if ($request->has('code')) {
            // todo: get token from epic, is this needed?
            // todo: authenticate in cpm
            event(new EpicSsoLoginEvent(0));

            return session()->get('url.intended', route('login'));
        }

        throw new \Exception('cannot handle request');
    }

    private function getAuthorizationCode(string $launchToken, MetadataResponse $metadataResponse)
    {
        return Http::post($metadataResponse->authorizeUrl, [
            'response_type' => 'code',
            'client_id'     => $this->getClientId(),
            'redirect_uri'  => urlencode(request()->url()),
            'scope'         => 'launch',
            'launch'        => $launchToken,
            'state'         => 'abcde12345',
        ]);
    }

    private function getClientId()
    {
        return isProductionEnv() ? config('epicsso.app_client_id') : config('epicsso.non_prod_client_id');
    }

    private function getMetadataEndpoint(string $iss): MetadataResponse
    {
        return Cache::remember($this->getClientId().'::'.$iss.'/metadata', 30, function () use ($iss) {
            $response = Http::withHeaders([
                'Accept'         => 'application/fhir+json',
                'Epic-Client-ID' => $this->getClientId(),
            ])->get($iss.'/metadata');

            return new MetadataResponse($response->json());
        });
    }
}
