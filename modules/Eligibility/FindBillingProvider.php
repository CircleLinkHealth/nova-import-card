<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility;

class FindBillingProvider
{
    public function from($args)
    {
        if (is_array($args)) {
        }

        if (isset($args['referring_provider_name'])) {
            return $args['referring_provider_name'];
        }
    }
}
