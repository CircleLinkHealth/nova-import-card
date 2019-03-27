<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Customer\Entities\Ehr;
use CircleLinkHealth\Customer\Entities\User;

class TargetPatient extends BaseModel
{
    protected $guarded = [];

    public function ehr()
    {
        return $this->belongsTo(Ehr::class, 'ehr_id');
    }

    public function enrollee()
    {
        return $this->belongsTo(Enrollee::class, 'enrollee_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
