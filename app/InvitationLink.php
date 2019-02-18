<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class InvitationLink extends Model
{
    protected $fillable = [
        'patient_info_id',
        'survey_id',
        'link_token',
        'is_manually_expired'
    ];

    protected $casts = [
        'is_manually_expired' => 'boolean'
    ];

    public function patientInfo()
    {
        return $this->belongsTo(Patient::class, 'patient_info_id');
    }
}
