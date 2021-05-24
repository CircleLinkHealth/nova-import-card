<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecord\Templates\Resources;

use Illuminate\Contracts\Support\Arrayable;

class Address implements Arrayable
{
    public ?string $city    = null;
    public ?string $country = null;
    public ?string $state   = null;
    public array $street    = [];
    public ?string $zip     = null;

    public function toArray()
    {
        return [
            'street'  => $this->street,
            'city'    => $this->city,
            'state'   => $this->state,
            'zip'     => $this->zip,
            'country' => $this->country,
        ];
    }
}
