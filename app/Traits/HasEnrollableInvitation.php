<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

use App\EnrollableInvitationLink;
use App\EnrollableRequestInfo;

trait HasEnrollableInvitation
{
    public function getLastEnrollmentInvitationLink()
    {
        return $this->enrollmentInvitationLink()->orderBy('created_at', 'desc')->first();
    }

    /**
     * @return mixed
     */
    public function enrollmentInvitationLink()
    {
        return $this->morphOne(EnrollableInvitationLink::class, 'invitationable');
    }

    /**
     * @return mixed
     */
    public function statusRequestsInfo()
    {
        return $this->morphOne(EnrollableRequestInfo::class, 'enrollable');
    }
}
