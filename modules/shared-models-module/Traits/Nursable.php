<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Traits;

use CircleLinkHealth\Customer\Entities\Nurse;

trait Nursable
{
    public function nurseInfo()
    {
        return $this->belongsTo(Nurse::class);
    }

    /**
     * Returns results belonging to the Nurse user_id.
     *
     * @param $builder
     * @param $userId
     *
     * @return mixed
     */
    public function scopeOfNurses($builder, $userId)
    {
        $usersId = parseIds($userId);

        return $builder->whereHas('nurseInfo', function ($q) use ($usersId) {
            $q->whereIn('user_id', $usersId);
        });
    }
}
