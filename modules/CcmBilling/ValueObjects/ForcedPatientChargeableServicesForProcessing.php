<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;

use CircleLinkHealth\CcmBilling\Entities\PatientForcedChargeableService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ForcedPatientChargeableServicesForProcessing
{
    protected string $actionType;
    protected string $chargeableServiceCode;

    //this assumes correct dates have been accounted for
    public static function fromCollection(EloquentCollection $collection): array
    {
        return $collection->map(
            fn (PatientForcedChargeableService $s) => self::fromRelationship($s)
        )
            ->filter()
            ->toArray();
    }

    public static function fromRelationship(PatientForcedChargeableService $s): ?self
    {
        if ( ! $s->chargeableService) {
            return null;
        }

        return (new static())
            ->setChargeableServiceCode($s->chargeableService->code)
            ->setActionType($s->action_type);
    }

    public function getChargeableServiceCode(): string
    {
        return $this->chargeableServiceCode;
    }

    public function isBlocked(): bool
    {
        return PatientForcedChargeableService::BLOCK_ACTION_TYPE === $this->actionType;
    }

    public function isForced(): bool
    {
        return PatientForcedChargeableService::FORCE_ACTION_TYPE === $this->actionType;
    }

    public function setActionType(string $actionType): self
    {
        $this->actionType = $actionType;

        return $this;
    }

    public function setChargeableServiceCode(string $chargeableServiceCode): self
    {
        $this->chargeableServiceCode = $chargeableServiceCode;

        return $this;
    }
}