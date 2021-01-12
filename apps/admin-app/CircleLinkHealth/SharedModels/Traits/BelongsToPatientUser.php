<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Traits;

use CircleLinkHealth\Customer\Entities\User;

trait BelongsToPatientUser
{
    /**
     * This is the patient that owns this resource.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id', 'id');
    }
}
