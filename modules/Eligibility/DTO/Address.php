<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\DTO;

class Address
{
    public $address;
    /**
     * @var null
     */
    public $address2;
    public $city;
    public $state;
    public $zip;

    public function __construct(
        string $address,
        string $city,
        string $state,
        string $zip,
        ?string $address2 = null
    ) {
        $this->address  = $address;
        $this->address2 = $address2;
        $this->city     = $city;
        $this->state    = $state;
        $this->zip      = $zip;
    }
}
