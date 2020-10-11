<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\PostmarkInboundCallback;

use Illuminate\Support\Collection;

class InboundCallbackHelpers
{
//    Maybe this helper is not necessary

    /**
     * @return bool
     */
    public static function multiMatch(Collection $postmarkInboundPatientsMatched)
    {
        return $postmarkInboundPatientsMatched->count() > 1;
    }

    public static function singleMatch(Collection $postmarkInboundPatientsMatched)
    {
        return 1 === $postmarkInboundPatientsMatched->count();
    }
}
