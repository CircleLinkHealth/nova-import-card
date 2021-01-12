<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

interface HasUniqueIdentifierForDebounce
{
    public function getUniqueIdentifier(): string;
}
