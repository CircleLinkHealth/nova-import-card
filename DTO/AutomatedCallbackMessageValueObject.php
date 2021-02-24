<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\DTO;

class AutomatedCallbackMessageValueObject
{
    public string $firstName;
    public string $lastName;
    public string $message;
    public string $phone;

    public function __construct(string $phone, string $message, string $firstName, string $lastName)
    {
        $this->phone     = $phone;
        $this->message   = $message;
        $this->firstName = $firstName;
        $this->lastName  = $lastName;
    }

    public function toCallbackMessage(): string
    {
        $phoneFormatted = formatPhoneNumberE164($this->phone);

        return 'From'.' '."[$phoneFormatted $this->firstName $this->lastName]: $this->message.".' '."Callback Number: $phoneFormatted";
    }
}
