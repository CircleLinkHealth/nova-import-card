<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Traits;

use CircleLinkHealth\Customer\Entities\EmrDirectAddress;

trait HasEmrDirectAddress
{
    public function emrDirect()
    {
        return $this->morphMany(EmrDirectAddress::class, 'emrDirectable');
    }

    public function getEmrDirectAddressAttribute()
    {
        return $this->emrDirect->first()->address ?? null;
    }

    public function hasEmrDirectAddress(): bool
    {
        return $this->emrDirect()->exists();
    }

    public function setEmrDirectAddressAttribute($address)
    {
        $this->emrDirect()->delete();

        if (empty($address)) {
            //assume we wanted to delete the previous address
            return true;
        }

        $this->emrDirect()->create(
            [
                'address' => $address,
            ]
        );
    }
}
