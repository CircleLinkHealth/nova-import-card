<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Contracts\Services\TwilioClientable;
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
}
