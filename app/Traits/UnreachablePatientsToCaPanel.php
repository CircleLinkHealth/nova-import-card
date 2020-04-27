<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;

trait UnreachablePatientsToCaPanel
{
    /**
     * We are doing this so we can assign enrollmet call and show the enrolle model on CA PANEL.
     */
    public function createEnrolleModelForPatientWithAssignedCall(User $user)
    {
        Enrollee::updateOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
                'practice_id'               => $user->primaryPractice->id,
                'dob'                       => $user->patientInfo->birth_date,
                'first_name'                => $user->first_name,
                'last_name'                 => $user->last_name,
                'address'                   => $user->address,
                'address_2'                 => $user->address2,
                'city'                      => $user->city,
                'state'                     => $user->state,
                'zip'                       => $user->zip,
                'primary_phone'             => $user->getPrimaryPhone(),
                'home_phone'                => $user->getHomePhoneNumber(),
                'cell_phone'                => $user->getMobilePhoneNumber(),
                'status'                    => Enrollee::TO_CALL,
                'enrollment_non_responsive' => true,
                'auto_enrollment_triggered' => true,
            ]
        );
    }
}
