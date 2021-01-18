<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;


use CircleLinkHealth\Customer\Entities\ChargeableService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ForcedPatientChargeableServicesForProcessing
{
    protected string $chargeableServiceCode;

    /**
     * @return string
     */
    public function getChargeableServiceCode(): string
    {
        return $this->chargeableServiceCode;
    }

    /**
     * @param string $chargeableServiceCode
     */
    public function setChargeableServiceCode(string $chargeableServiceCode): self
    {
        $this->chargeableServiceCode = $chargeableServiceCode;

        return $this;
    }

    protected bool $isForced;

    /**
     * @return bool
     */
    public function isForced(): bool
    {
        return $this->isForced;
    }

    /**
     * @param bool $isForced
     */
    public function setIsForced(bool $isForced): self
    {
        $this->isForced = $isForced;

        return $this;
    }

    public static function fromRelationship(ChargeableService $cs) :? self
    {
        if (! $cs->pivot){
            return null;
        }

        return (new static)
            ->setChargeableServiceCode($cs->code)
            ->setIsForced($cs->pivot->is_forced);
    }

    public static function fromCollection(EloquentCollection $collection):array
    {
        return $collection->map(
            fn(ChargeableService $cs) => self::fromRelationship($cs)
        )
                          ->filter()
                          ->toArray();
    }
    //todo: should we have date here?
}