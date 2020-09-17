<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Entities;

class TwilioInboundSmsRequest
{
    public ?string $AccountSid;
    public ?string $ApiVersion;
    public ?string $Body;
    public ?string $From;
    public ?string $FromCity;
    public ?string $FromCountry;
    public ?string $FromState;
    public ?string $FromZip;
    public ?string $MessageSid;
    public ?string $NumMedia;
    public ?string $NumSegments;
    public ?string $SmsMessageSid;
    public ?string $SmsSid;
    public ?string $SmsStatus;
    public ?string $To;
    public ?string $ToCity;
    public ?string $ToCountry;
    public ?string $ToState;
    public ?string $ToZip;

    public function __construct(array $input)
    {
        foreach ($input as $key => $value) {
            $this->$key = $value;
        }
    }
}
