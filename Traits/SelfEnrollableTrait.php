<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Traits;

use CircleLinkHealth\Customer\EnrollableInvitationLink\EnrollableInvitationLink;
use CircleLinkHealth\Customer\EnrollableRequestInfo\EnrollableRequestInfo;

trait SelfEnrollableTrait
{
    /**
     * @return mixed
     */
    public function enrollmentInvitationLinks()
    {
        return $this->morphMany(EnrollableInvitationLink::class, 'invitationable');
    }

    public function getLastEnrollmentInvitationLink(): ?EnrollableInvitationLink
    {
        return $this->enrollmentInvitationLinks()->orderBy('created_at', 'desc')->first();
    }

    /**
     * @return mixed
     */
    public function statusRequestsInfo()
    {
        return $this->morphOne(EnrollableRequestInfo::class, 'enrollable');
    }
}
