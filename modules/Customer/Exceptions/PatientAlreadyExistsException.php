<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Exceptions;

class PatientAlreadyExistsException extends \Exception
{
    /**
     * @var int
     */
    private $patientUserId;

    public function __construct(int $patientUserId)
    {
        parent::__construct("This patient is a duplicate of $patientUserId");
        $this->patientUserId = $patientUserId;
    }

    public function getPatientUserId(): int
    {
        return $this->patientUserId;
    }
}
