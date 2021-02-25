<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Exceptions;

class NurseNotFoundException extends \Exception
{
    public function __construct(int $forPatientId)
    {
        parent::__construct("Could not find nurse for patient $forPatientId");
    }
}
