<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature\UserScope\Assertions;

class Practice implements Assertion
{
    public string $key;
    public string $lookIn;
    public ?string $billingProviderId;
    
    public function __construct(string $lookIn, string $key, string $billingProviderId = null)
    {
        $this->lookIn = $lookIn;
        $this->key    = $key;
        $this->billingProviderId = $billingProviderId;
    }
}
