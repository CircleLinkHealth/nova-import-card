<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core;

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
     * Download media from Twilio and store on disk.
     *
     * @param $url - media url to download from Twilio
     *
     * @return array - Return array with errorCode, errorDetail and mediaUrl. If error, mediaUrl is null.
     */
    public function downloadMedia($url);

    /**
     * Generate a capability token.
     */
    public function generateCapabilityToken(): string;

    /**
     * Get an instance of a the Twilio REST API Client.
     */
    public function getClient(): Client;
}
