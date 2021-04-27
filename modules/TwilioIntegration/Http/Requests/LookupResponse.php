<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TwilioIntegration\Http\Requests;

use CircleLinkHealth\TwilioIntegration\Models\TwilioLookup;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Lookups\V1\PhoneNumberInstance;

class LookupResponse
{
    public ?string $carrierName  = null;
    public ?string $errorCode    = null;
    public ?string $errorDetails = null;
    public ?bool $isMobile       = null;
    public string $phoneNumber;

    public static function fromApiError(string $phoneNumber, TwilioException $e): LookupResponse
    {
        $result               = new LookupResponse();
        $result->phoneNumber  = $phoneNumber;
        $result->errorCode    = $e->getCode();
        $result->errorDetails = $e->getMessage();

        return $result;
    }

    public static function fromApiResponse(PhoneNumberInstance $instance): LookupResponse
    {
        $result              = new LookupResponse();
        $result->phoneNumber = $instance->phoneNumber;
        if ( ! empty($instance->carrier)) {
            $result->carrierName = $instance->carrier['name'];
            $result->isMobile    = 'mobile' === $instance->carrier['type'];
        }

        return $result;
    }

    public static function fromDb(TwilioLookup $twilioLookup): LookupResponse
    {
        $result               = new LookupResponse();
        $result->phoneNumber  = $twilioLookup->phone_number;
        $result->isMobile     = $twilioLookup->is_mobile;
        $result->carrierName  = $twilioLookup->carrier;
        $result->errorCode    = $twilioLookup->api_error_code;
        $result->errorDetails = $twilioLookup->api_error_details;

        return $result;
    }
}
