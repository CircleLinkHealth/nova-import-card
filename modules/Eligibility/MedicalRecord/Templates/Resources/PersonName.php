<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecord\Templates\Resources;

use Illuminate\Contracts\Support\Arrayable;

class PersonName implements Arrayable
{
    public ?string $family = null;
    public array $given    = [];
    public ?string $prefix = null;
    public ?string $suffix = null;

    public function toArray()
    {
        return [
            'prefix' => $this->prefix,
            'given'  => $this->given,
            'family' => $this->family,
            'suffix' => $this->suffix,
        ];
    }
}
