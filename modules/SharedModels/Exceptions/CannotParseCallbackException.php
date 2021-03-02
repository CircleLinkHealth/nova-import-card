<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Exceptions;

class CannotParseCallbackException extends \Exception
{
    public function __construct(string $error)
    {
        parent::__construct("Inbound Callback could not be parsed: $error");
    }
}
