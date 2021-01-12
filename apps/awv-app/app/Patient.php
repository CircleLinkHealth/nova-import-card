<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

/**
 * Class Patient.
 *
 * @property bool $is_awv
 */
class Patient extends \CircleLinkHealth\Customer\Entities\Patient
{
    /**
     * CCM_STATUS for AWV.
     */
    const NA = 'n/a';

    public function url()
    {
        return $this->hasMany(InvitationLink::class, 'patient_info_id');
    }
}
