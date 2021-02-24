<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Exceptions;

class EnrolleeWithoutUserException extends \Exception
{
    public function __construct(int $enrolleeId)
    {
        parent::__construct("Enrolee[$enrolleeId] does not have a user yet. Should not have reached here.");
    }
}
