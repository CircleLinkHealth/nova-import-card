<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts\Services;

use Twilio\Jwt\ClientToken;
use Twilio\Rest\Client;

/**
 * Created by IntelliJ IDEA.
 * User: pangratioscosma
 * Date: 07/12/2018
 * Time: 12:24 AM.
 */
interface TwilioClientable
{
    /**
     * Generate a capability token.
     *
     * @return ClientToken
     */
    public function generateCapabilityToken(): ClientToken;

    /**
     * Get an instance of a the Twilio REST API Client.
     *
     * @return Client
     */
    public function getClient(): Client;
}
