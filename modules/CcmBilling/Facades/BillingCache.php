<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Facades;

use CircleLinkHealth\CcmBilling\Caches\BillingCache as BillingCacheInterface;
use Illuminate\Support\Facades\Facade;

class BillingCache extends Facade
{
    protected static function getFacadeAccessor()
    {
        return BillingCacheInterface::class;
    }
}
