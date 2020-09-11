<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Entities;

class EmailAddressParts
{
    public string $domain;
    public string $username;

    /**
     * EmailAddressParts constructor.
     */
    public function __construct(string $username, string $domain)
    {
        $this->username = $username;
        $this->domain   = $domain;
    }
}
