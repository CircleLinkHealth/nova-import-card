<?php

namespace App;

use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * @property int survey_id
 * @property Carbon updated_at
 * @property bool is_manually_expired
 * @property Patient|null patientInfo
 */
class InvitationLink extends BaseModel
{
    protected $fillable = [
        'patient_info_id',
        'survey_id',
        'link_token',
        'is_manually_expired',
    ];

    protected $casts = [
        'is_manually_expired' => 'boolean',
    ];

    public function patientInfo()
    {
        return $this->belongsTo(Patient::class, 'patient_info_id');
    }
}
