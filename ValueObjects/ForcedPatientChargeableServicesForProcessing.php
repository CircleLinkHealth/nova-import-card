<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;

use CircleLinkHealth\CcmBilling\Entities\PatientForcedChargeableService;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ForcedPatientChargeableServicesForProcessing
{
    protected string $actionType;
    protected string $chargeableServiceCode;

    public static function fromCollection(EloquentCollection $collection): array
    {
        return $collection->map(
            fn (ChargeableService $cs) => self::fromRelationship($cs)
        )
            ->filter()
            ->toArray();
    }

    public static function fromRelationship(ChargeableService $cs): ?self
    {
        if ( ! $cs->forcedDetails) {
            return null;
        }

        return (new static())
            ->setChargeableServiceCode($cs->code)
            ->setActionType($cs->forcedDetails->action_type);
    }

    public function getChargeableServiceCode(): string
    {
        return $this->chargeableServiceCode;
    }

    public function isForced(): bool
    {
        return PatientForcedChargeableService::FORCE_ACTION_TYPE === $this->actionType;
    }

    public function setActionType(bool $actionType): self
    {
        $this->actionType = $actionType;

        return $this;
    }

    public function setChargeableServiceCode(string $chargeableServiceCode): self
    {
        $this->chargeableServiceCode = $chargeableServiceCode;

        return $this;
    }

    //todo: should we have date here?
}
