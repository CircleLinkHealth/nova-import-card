<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility;

use CircleLinkHealth\SharedModels\Entities\Ccda;

trait BelongsToCcda
{
    public function ccda()
    {
        return $this->belongsTo(Ccda::class);
    }
}
