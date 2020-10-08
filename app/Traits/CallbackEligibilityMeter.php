<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

use App\Services\Postmark\PostmarkInboundCallbackMatchResults;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait CallbackEligibilityMeter
// Help naming this trait?
{
    private function singleMatch(Collection $postmarkInboundPatientsMatched)
    {
        return 1 === $postmarkInboundPatientsMatched->count();
    }
}
