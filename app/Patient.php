<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Patient extends \CircleLinkHealth\Customer\Entities\Patient
{
    public function url()
    {
        return $this->hasMany(InvitationLink::class, 'patient_info_id');
    }

}
