<?php
/**
 * Created by IntelliJ IDEA.
 * User: pangratioscosma
 * Date: 07/12/2018
 * Time: 12:05 AM
 */

namespace App\Services;


use App\Contracts\Services\TwilioClientable;
use Twilio\Jwt\ClientToken;
use Twilio\Rest\Client;

class TwilioClientService implements TwilioClientable
{

    private $client;
    private $capability;

    /**
     * TwilioClientService constructor.
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public function __construct()
    {
        $this->client     = new Client(config('services.twilio.sid'), config('services.twilio.token'));
        $this->capability = new ClientToken(config('services.twilio.sid'), config('services.twilio.token'));
        $this->capability->allowClientOutgoing(config('services.twilio.twiml-app-sid'));
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function generateCapabilityToken(): ClientToken
    {
        return $this->capability->generateToken();
    }
}