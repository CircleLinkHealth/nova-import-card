<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;


use Carbon\Carbon;

class ForceAttachInputDTO
{
    protected int $patientUserId;

    /**
     * @return int
     */
    public function getPatientUserId(): int
    {
        return $this->patientUserId;
    }

    /**
     * @param int $patientUserId
     */
    public function setPatientUserId(int $patientUserId): self
    {
        $this->patientUserId = $patientUserId;
        return $this;
    }

    /**
     * @return int
     */
    public function getChargeableServiceId(): int
    {
        return $this->chargeableServiceId;
    }

    /**
     * @param int $chargeableServiceId
     */
    public function setChargeableServiceId(int $chargeableServiceId): self
    {
        $this->chargeableServiceId = $chargeableServiceId;
        return $this;
    }

    /**
     * @return string
     */
    public function getActionType(): string
    {
        return $this->actionType;
    }

    /**
     * @param string $actionType
     */
    public function setActionType(string $actionType): self
    {
        $this->actionType = $actionType;
        return $this;
    }

    /**
     * @return Carbon|null
     */
    public function getMonth(): ?Carbon
    {
        return $this->month;
    }

    /**
     * @param Carbon|null $month
     */
    public function setMonth(?Carbon $month): self
    {
        $this->month = $month;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDetaching(): bool
    {
        return $this->isDetaching;
    }

    /**
     * @param bool $isDetaching
     */
    public function setIsDetaching(bool $isDetaching): self
    {
        $this->isDetaching = $isDetaching;
        return $this;
    }

    /**
     * @return Carbon|null
     */
    public function getEntryCreatedAt(): ?Carbon
    {
        return $this->entryCreatedAt;
    }

    /**
     * @param Carbon|null $entryCreatedAt
     */
    public function setEntryCreatedAt(?Carbon $entryCreatedAt): self
    {
        $this->entryCreatedAt = $entryCreatedAt;
        return $this;
    }

    protected int $chargeableServiceId;
    protected string $actionType;
    protected ?Carbon $month;
    protected bool $isDetaching = false;
    protected ?Carbon $entryCreatedAt;
}