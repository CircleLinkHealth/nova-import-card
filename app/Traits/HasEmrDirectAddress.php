<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 23/02/2017
 * Time: 7:46 PM
 */

namespace App\Traits;

use App\EmrDirectAddress;

trait HasEmrDirectAddress
{
    public function getEmrDirectAddressAttribute()
    {
        return $this->emrDirect->first()->address ?? null;
    }

    public function setEmrDirectAddressAttribute($address)
    {
        $this->emrDirect()->delete();

        if (empty($address)) {
            //assume we wanted to delete the previous address
            return true;
        }

        $this->emrDirect()->create([
            'address' => $address,
        ]);
    }

    public function emrDirect()
    {
        return $this->morphMany(EmrDirectAddress::class, 'emrDirectable');
    }
}
