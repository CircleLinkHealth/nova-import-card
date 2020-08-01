<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Entries for the db table are created from SQL triggers existing on the patient_info table.
 */
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

    public function scopeOfDate($query, Carbon $startDate, Carbon $endDate = null)
    {
        $startDate = $startDate->copy()->startOfDay();
        $endDate   = $endDate ? $endDate->copy()->endOfDay() : $startDate->copy()->endOfDay();

        return $query->where([
            ['created_at', '>=', $startDate],
            ['created_at', '<=', $endDate],
        ]);
    }
}
