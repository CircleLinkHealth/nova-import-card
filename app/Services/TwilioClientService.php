<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use CircleLinkHealth\Core\TwilioClientable;
use Twilio\Jwt\ClientToken;
use Twilio\Rest\Client;

class TwilioClientService implements TwilioClientable
{
    private const TOKEN_LIFETIME_SECONDS = 7200; //2 hours
    private $capability;
    private $client;

    /**
     * Download media from Twilio Cloud.
     *
     * @param $url
     *
     * @return array of [ errorCode, errorDetail, mediaUrl (path on disk) ]
     */
    public function downloadMedia($url)
    {
        $accountSid = config('services.twilio.sid');
        $token      = config('services.twilio.token');
        $path       = 'tmp/twilio-recordings/';
        $path       = $path.basename($url);

        try {
            $c = new \GuzzleHttp\Client();

            //this could fail with 401 Authorization
            //or if cannot write file
            $c->get($url, [
                'sink' => $path,
                'auth' => [
                    $accountSid,
                    $token,
                ],
            ]);

            //this could fail if file does not exist
            $str = file_get_contents($path, false, null, 0, 500);

            //check the file that was downloaded
            //if we have XML, it means that there was an error downloading the mp3
            $xml = $this->parseXMLFromString($str);
            if ($xml) {
                unlink($path);

                return [
                    'errorCode'   => $xml->RestException->Code,
                    'errorDetail' => $xml->RestException->Detail,
                    'mediaUrl'    => null,
                ];
            }
        } catch (\Exception $e) {
            return [
                //make sure code is not 0, 0 means no error
                'errorCode' => 0 === $e->getCode()
                    ? 1
                    : $e->getTrace(),
                'errorDetail' => $e->getMessage(),
                'mediaUrl'    => null,
            ];
        }

        return [
            'errorCode'   => 0,
            'errorDetail' => null,
            'mediaUrl'    => $path,
        ];
    }

    /**
     * Generates new token based on credentials and permissions set
     * in the constructor.
     */
    public function generateCapabilityToken(): string
    {
        if ( ! $this->capability) {
            $this->capability = new ClientToken(config('services.twilio.account_sid'), config('services.twilio.auth_token'));
            $this->capability->allowClientOutgoing(config('services.twilio.twiml-app-sid'));
        }

        return $this->capability->generateToken(TwilioClientService::TOKEN_LIFETIME_SECONDS);
    }

    public function getClient(): Client
    {
        if ( ! $this->client) {
            $this->client = new Client(config('services.twilio.account_sid'), config('services.twilio.auth_token'));
        }

        return $this->client;
    }

    /**
     * @param $str - an XML string
     *
     * @return \SimpleXMLElement|null - return the parsed xml document or null if cannot be parsed
     */
    private function parseXMLFromString($str)
    {
        libxml_use_internal_errors(true);

        return simplexml_load_string($str);
    }
}
