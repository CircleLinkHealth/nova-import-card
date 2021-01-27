<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;

use Carbon\Carbon;

class ForceAttachInputDTO
{
    protected string $actionType;

    protected int $chargeableServiceId;
    protected ?Carbon $entryCreatedAt;
    protected bool $isDetaching = false;
    protected ?Carbon $month;
    protected int $patientUserId;

    public function getActionType(): string
    {
        return $this->actionType;
    }

    public function getChargeableServiceId(): int
    {
        return $this->chargeableServiceId;
    }

    public function getEntryCreatedAt(): ?Carbon
    {
        return $this->entryCreatedAt;
    }

    public function getMonth(): ?Carbon
    {
        return $this->month;
    }

    public function getPatientUserId(): int
    {
        return $this->patientUserId;
    }

    public function isDetaching(): bool
    {
        return $this->isDetaching;
    }

    public function setActionType(string $actionType): self
    {
        $this->actionType = $actionType;

        return $this;
    }

    public function setChargeableServiceId(int $chargeableServiceId): self
    {
        $this->chargeableServiceId = $chargeableServiceId;

        return $this;
    }

    public function setEntryCreatedAt(?Carbon $entryCreatedAt): self
    {
        $this->entryCreatedAt = $entryCreatedAt;

        return $this;
    }

    public function setIsDetaching(bool $isDetaching): self
    {
        $this->isDetaching = $isDetaching;

        return $this;
    }

    public function setMonth(?Carbon $month): self
    {
        $this->month = $month;

        return $this;
    }

    public function setPatientUserId(int $patientUserId): self
    {
        $this->patientUserId = $patientUserId;

        return $this;
    }
}
