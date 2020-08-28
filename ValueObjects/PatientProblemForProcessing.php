<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;

class PatientProblemForProcessing
{
    protected string $code;
    protected int $id;

    protected array $serviceCodes;

    public function getCode(): string
    {
        return $this->code;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getServiceCodes(): array
    {
        return $this->serviceCodes;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setServiceCodes(array $serviceCodes): self
    {
        $this->serviceCodes = $serviceCodes;

        return $this;
    }
}
