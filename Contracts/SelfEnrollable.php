<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Contracts;

use CircleLinkHealth\Customer\EnrollableInvitationLink\EnrollableInvitationLink;

/**
 * This Model represents an Entity that can enroll themselves to CPM.
 *
 * Examples of such models:
 *  - User (survey only users, unreachable patients)
 *  - Enrollee
 *
 * Interface SelfEnrollable
 */
interface SelfEnrollable
{
    public function enrollmentInvitationLinks();

    public function getLastEnrollmentInvitationLink(): ?EnrollableInvitationLink;

    public function statusRequestsInfo();
}
