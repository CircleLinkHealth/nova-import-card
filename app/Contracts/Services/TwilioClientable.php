<?php

namespace App\Contracts\Services;

use Twilio\Rest\Client;
use Twilio\Jwt\ClientToken;

/**
 * Created by IntelliJ IDEA.
 * User: pangratioscosma
 * Date: 07/12/2018
 * Time: 12:24 AM
 */
interface TwilioClientable
{

    /**
     * Get an instance of a the Twilio REST API Client.
     *
     * @return Client
     */
    public function getClient(): Client;

    /**
     * Generate a capability token.
     *
     * @return ClientToken
     */
    public function generateCapabilityToken(): ClientToken;
}