<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature\UserScope\Assertions;

class Location implements Assertion
{
    public ?string $billingProviderId;
    public string $key;
    public string $lookIn;

    public function __construct(string $lookIn, string $locationIdKey, string $billingProviderId = null)
    {
        $this->lookIn            = $lookIn;
        $this->key               = $locationIdKey;
        $this->billingProviderId = $billingProviderId;
    }
}
