<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Contracts\Services\TwilioClientable;
use Symfony\Component\Process\Process;
use Twilio\Jwt\ClientToken;
use Twilio\Rest\Client;

class TwilioClientService implements TwilioClientable
{
    private $capability;

    private $client;

    /**
     * TwilioClientService constructor.
     *
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public function __construct()
    {
        $this->client     = new Client(config('services.twilio.sid'), config('services.twilio.token'));
        $this->capability = new ClientToken(config('services.twilio.sid'), config('services.twilio.token'));
        $this->capability->allowClientOutgoing(config('services.twilio.twiml-app-sid'));
    }

    public function generateCapabilityToken(): string
    {
        return $this->capability->generateToken();
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function downloadMedia($url)
    {
        $accountSid = config('services.twilio.sid');
        $token      = config('services.twilio.token');
        $path       = 'tmp/twilio-recordings/';
        $path       = $path . basename($url);

        //todo - try these
//        $c = new \GuzzleHttp\Client();
//        $c->get();

//        $p = new Process("curl -u $accountSid:$token $url --create-dirs -o $path");
//        $x = $p->run();

        exec("curl -u $accountSid:$token $url --create-dirs -o $path");

        //check the file that was downloaded
        $str = file_get_contents($path, false, null, 0, 500);
        $xml = $this->parseXMLFromString($str);
        if ($xml) {
            unlink($path);
            return [
                'errorCode' => $xml->RestException->Code,
                'errorDetail' => $xml->RestException->Detail,
                'mediaUrl' => null
            ];
        }

        return [
            'errorCode' => 0,
            'errorDetail' => null,
            'mediaUrl' => $path
        ];
    }

    /**
     * @param $str - an XML string
     *
     * @return \SimpleXMLElement|null - return the parsed xml document or null if cannot be parsed
     */
    private function parseXMLFromString($str) {
        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($str);
        return $doc;
    }
}
