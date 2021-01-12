<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

interface NotificationAboutPatient
{
    /**
     * The user ID of the Patient.
     */
    public function notificationAboutPatientWithUserId(): int;
}
