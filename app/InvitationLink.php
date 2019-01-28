<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvitationLink extends Model
{
    protected $guarded = [];
    protected $casts = [
        'is_expired' => 'boolean'
    ];
    protected $table = 'invitation_links';

    public function patient()
    {
        return $this->belongsTo(awvPatients::class, 'patient_user_id');
    }
}
