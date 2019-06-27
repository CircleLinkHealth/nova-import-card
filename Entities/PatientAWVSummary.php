<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use Illuminate\Database\Eloquent\Model;

class PatientAWVSummary extends Model
{
    protected $fillable = [
        'patient_id',
        'year',
        'is_initial_visit',
        'is_billable',
        'completed_at',
    ];
    protected $table = 'patient_awv_summaries';

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
