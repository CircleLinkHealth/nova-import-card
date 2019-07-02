<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ResponseCache\Exceptions;

use Exception;

class CouldNotUnserialize extends Exception
{
    public static function serializedResponse(string $serializedResponse): self
    {
        return new static("Could not unserialize `{$serializedResponse}`");
    }
}
