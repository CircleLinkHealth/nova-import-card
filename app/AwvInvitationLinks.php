<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AwvInvitationLinks extends Model
{
    protected $fillable = [
        'patient_user_id',
        'patient_name',
        'birth_date',
        'survey_id',
        'link_token',
        //'token',
        'is_expired',
    ];

    protected $table = 'awv_invitation_links';

    public function patient()
    {//Patient model belongs to CPM. Are we going to use this model or create another??
        return $this->belongsTo(Patient::class, 'patient_user_id');
    }
}
