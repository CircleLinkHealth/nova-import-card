<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvitationLink extends Model
{
    protected $fillable = [
        'aw_patient_id',
        'survey_id',
        'link_token',
        'is_expired'
    ];
    protected $casts = [
        'is_expired' => 'boolean'
    ];
   // protected $table = 'invitation_links';

    public function patient()
    {
        return $this->belongsTo(awvPatients::class);
    }
}
