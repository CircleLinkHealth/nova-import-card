<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature\UserScope\Assertions;

class CareTeam implements Assertion
{
    public string $key;
    public string $lookIn;

    public function __construct(string $lookIn, string $key)
    {
        $this->lookIn = $lookIn;
        $this->key    = $key;
    }
}
