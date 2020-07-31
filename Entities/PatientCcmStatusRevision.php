<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Model;

class PatientCcmStatusRevision extends Model
{
    protected $fillable = [];

    protected $table = 'patient_ccm_status_revisions';

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_info_id');
    }

    public function patientUser()
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }
}
