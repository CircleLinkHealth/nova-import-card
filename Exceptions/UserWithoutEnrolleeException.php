<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Exceptions;

class UserWithoutEnrolleeException extends \Exception
{
    public function __construct(int $forPatientId)
    {
        parent::__construct("Enrollee not found in user[$forPatientId]");
    }
}
