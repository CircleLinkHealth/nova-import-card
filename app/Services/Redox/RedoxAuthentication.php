<?php namespace App\Services\Redox;

use App\ThirdPartyApiConfig;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Request;

class RedoxAuthentication
{

    protected $apiKey;

    protected $apiSecret;

    public function __construct()
    {
        $retrieveApiKey = ThirdPartyApiConfig::select('meta_value')
            ->whereMetaKey('redox_api_key')
            ->whereNotNull('meta_value')
            ->firstOrFail();

        $retrieveApiSecret = ThirdPartyApiConfig::select('meta_value')
            ->whereMetaKey('redox_api_secret')
            ->whereNotNull('meta_value')
            ->firstOrFail();

        $this->apiKey = $retrieveApiKey['meta_value'];
        $this->apiSecret = $retrieveApiSecret['meta_value'];
    }

    public function authenticate()
    {
        $client = new Client();

        $response = $client->post('https://www.redoxengine.com/api/auth/authenticate', [
            'json' => [
                'apiKey' => $this->apiKey,
                'secret' => $this->apiSecret
            ]
        ]);

        $body = json_decode($response->getBody(), true);

        $this->saveCredentialsToDb($body);
    }

    public function authenticateWithRefreshToken($refreshToken)
    {
        $client = new Client();

        $response = $client->post('https://www.redoxengine.com/api/auth/refreshToken', [
            'json' => [
                'apiKey' => $this->apiKey,
                'refreshToken' => $refreshToken
            ]
        ]);

        $body = json_decode($response->getBody(), true);

        $this->saveCredentialsToDb($body);
    }

    protected function saveCredentialsToDb($credentials)
    {
        ThirdPartyApiConfig::updateOrCreate([
            'meta_key' => 'redox_access_token'
        ], [
            'meta_key' => 'redox_access_token',
            'meta_value' => $credentials['accessToken']
        ]);

        ThirdPartyApiConfig::updateOrCreate([
            'meta_key' => 'redox_expires'
        ], [
            'meta_key' => 'redox_expires',
            'meta_value' => $credentials['expires']
        ]);

        ThirdPartyApiConfig::updateOrCreate([
            'meta_key' => 'redox_refresh_token'
        ], [
            'meta_key' => 'redox_refresh_token',
            'meta_value' => $credentials['refreshToken']
        ]);
    }
}
