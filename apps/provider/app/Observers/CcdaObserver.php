<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\Constants;
use CircleLinkHealth\SharedModels\Entities\Ccda;

class CcdaObserver
{
    public function saved(Ccda $ccda)
    {
        if ($ccda->isDirty('patient_id')) {
            $cacheKey = str_replace('{$userId}', $ccda->patient_id, Constants::CACHE_USER_HAS_CCDA);

            \Cache::forget($cacheKey);
        }
    }
}
