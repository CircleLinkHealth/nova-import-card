<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TwoFA\Providers;

use Authy\AuthyApi;
use CircleLinkHealth\TwoFA\Contracts\AuthyApiable;
use CircleLinkHealth\TwoFA\Decorators\AuthyResponseLogger;
use GuzzleHttp\Client;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class AuthyServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function provides()
    {
        return [
            AuthyApiable::class,
        ];
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->bind(AuthyApiable::class, function () {
            $api_key = config('services.authy.api_key');
            $api_url = config('services.authy.api_url');

            $authyApi = new AuthyApi($api_key, $api_url);

            //api to generate QR code is not included in the Authy SDK (3.0)
            //copied from AuthyApi constructor
            $client_opts = [
                'base_uri'    => "{$api_url}/",
                'headers'     => ['X-Authy-API-Key' => $api_key],
                'http_errors' => false,
                'curl'        => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4],
            ];
            $httpClient = new Client($client_opts);

            return new AuthyResponseLogger($authyApi, $httpClient);
        });
    }
}
