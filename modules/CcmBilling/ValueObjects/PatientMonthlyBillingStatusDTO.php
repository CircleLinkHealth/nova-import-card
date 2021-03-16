<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\PatientMonthlyBillingStatus;

class PatientMonthlyBillingStatusDTO
{
    protected ?int $actorId   = null;
    protected ?Carbon $month  = null;
    protected ?string $status = null;

    public static function fromModel(?PatientMonthlyBillingStatus $model = null): self
    {
        $static = new static();
        if (is_null($model)) {
            return $static;
        }

        return $static->setMonth($model->chargeable_month)
            ->setActorId($model->actor_id)
            ->setStatus($model->status);
    }

    public function getActorId(): ?int
    {
        return $this->actorId;
    }

    public function getMonth(): ?Carbon
    {
        return $this->month;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function isTouched(): bool
    {
        return ! is_null($this->actorId);
    }

    public function setActorId(?int $actorId): self
    {
        $this->actorId = $actorId;

        return $this;
    }

    public function setMonth(?Carbon $month): self
    {
        $this->month = $month;

        return $this;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
