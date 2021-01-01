<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Contracts;

interface HasUniqueIdentifierForDebounce
{
    public function getUniqueIdentifier(): string;
}
