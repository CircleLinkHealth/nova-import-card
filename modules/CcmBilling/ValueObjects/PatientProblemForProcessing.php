<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;

class PatientProblemForProcessing
{
    protected string $code;
    protected int $id;

    protected bool $isAttestedForMonth = false;
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

    public function isAttestedForMonth(): bool
    {
        return $this->isAttestedForMonth;
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

    public function setIsAttestedForMonth(bool $isAttestedForMonth): self
    {
        //todo: cover the following edge case:
        //what if a condition is attested for the previous month
        //and is deleted in current month
        //therefore when we try to process last month's billing
        //we can't actually find if it's attested
        $this->isAttestedForMonth = $isAttestedForMonth;

        return $this;
    }

    public function setServiceCodes(array $serviceCodes): self
    {
        $this->serviceCodes = $serviceCodes;

        return $this;
    }
}
