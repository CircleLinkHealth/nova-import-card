<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use CircleLinkHealth\Core\TwilioClientable;
use Log;
use Symfony\Component\Intl\Exception\NotImplementedException;
use Twilio\Exceptions\TwilioException;
use Twilio\Jwt\ClientToken;
use Twilio\Rest\Client;

class TwilioClientService implements TwilioClientable
{
    private const TOKEN_LIFETIME_SECONDS = 7200; //2 hours

    /**
     * @var ClientToken
     */
    private $capability;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var int
     */
    private $outgoingNumber;

    /**
     * TwilioClientService constructor.
     *
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public function __construct()
    {
        $this->client     = new Client(config('services.twilio.account_sid'), config('services.twilio.auth_token'));
        $this->capability = new ClientToken(
            config('services.twilio.account_sid'),
            config('services.twilio.auth_token')
        );
        $this->capability->allowClientOutgoing(config('services.twilio.twiml_app_sid'));
        $this->outgoingNumber = config('services.twilio.from');
    }

    public function downloadMedia($url)
    {
        throw new NotImplementedException('downloadMedia not implemented. see cpm project if you need this.');
    }

    /**
     * Generates new token based on credentials and permissions set
     * in the constructor.
     */
    public function generateCapabilityToken(): string
    {
        return $this->capability->generateToken(self::TOKEN_LIFETIME_SECONDS);
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @param int $to
     *
     * @throws TwilioException
     * @return string          the message id
     */
    public function sendSMS(string $to, string $text)
    {
        try {
            $arr = [
                'from' => $this->outgoingNumber,
                'body' => $text,
            ];

            if ( ! app()->isLocal()) {
                $arr['statusCallback'] = route('twilio.sms.status');
            }

            $resp = $this->client
                ->messages
                ->create($to, $arr);

            return $resp->sid;
        } catch (TwilioException $e) {
            Log::error("failed sending sms: {$e->getMessage()}");
            throw $e;
        }
    }
}
