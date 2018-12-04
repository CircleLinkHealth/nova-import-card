<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits\Relationships;

use App\User;

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
