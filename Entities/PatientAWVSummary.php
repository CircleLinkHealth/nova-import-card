<?php

namespace CircleLinkHealth\Customer\Entities;

use Illuminate\Database\Eloquent\Model;

class PatientAWVSummary extends Model
{
    protected $table = 'patient_awv_summaries';

    protected $fillable = [
        'patient_id',
        'month_year',
        'initial_visit',
        'subsequent_visit',
        'is_billable',
        'completed_at'
    ];

    public function patient(){
        return $this->belongsTo(User::class, 'patient_id');
    }
}
