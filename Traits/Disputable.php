<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Traits;

use CircleLinkHealth\SharedModels\Entities\Dispute;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/24/19
 * Time: 1:36 AM.
 */
trait Disputable
{
    public function dispute()
    {
        return $this->morphOne(Dispute::class, 'disputable');
    }
}
