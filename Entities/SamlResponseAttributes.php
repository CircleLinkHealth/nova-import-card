<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SamlSp\Entities;

class SamlResponseAttributes
{
    public ?string $patientId;
    public string $userId;

    /**
     * SamlResponseAttributes constructor.
     */
    public function __construct(string $userId, string $patientId = null)
    {
        $this->userId    = $userId;
        $this->patientId = $patientId;
    }
}
