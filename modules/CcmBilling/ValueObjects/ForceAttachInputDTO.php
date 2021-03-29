<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\PatientForcedChargeableService;

class ForceAttachInputDTO
{
    protected string $actionType = PatientForcedChargeableService::FORCE_ACTION_TYPE;

    protected int $chargeableServiceId;
    protected ?Carbon $entryCreatedAt = null;
    protected bool $isDetaching       = false;
    protected ?Carbon $month          = null;
    protected int $patientUserId;
    protected ?string $reason = null;

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

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function isDetaching(): bool
    {
        return $this->isDetaching;
    }

    public function isPermanent(): bool
    {
        return is_null($this->month);
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

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }
}
