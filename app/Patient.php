<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Patient
 * @package App
 *
 * @property bool $is_awv
 */
class Patient extends \CircleLinkHealth\Customer\Entities\Patient
{
    /**
     * CCM_STATUS for AWV
     */
    const NA = 'n/a';

    public function url()
    {
        return $this->hasMany(InvitationLink::class, 'patient_info_id');
    }

}
