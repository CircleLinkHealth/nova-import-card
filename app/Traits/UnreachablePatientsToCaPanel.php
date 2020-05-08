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
     * We are doing this so we can assign enrollmet call and show the enrollee model on CA PANEL.
     */
    public function createEnrolleModelForPatient(User $user)
    {
        Enrollee::updateOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
                'practice_id'             => $user->primaryPractice->id,
                'dob'                     => $user->patientInfo->birth_date,
                'first_name'              => $user->first_name,
                'last_name'               => $user->last_name,
                'address'                 => $user->address,
                'address_2'               => $user->address2,
                'city'                    => $user->city,
                'state'                   => $user->state,
                'zip'                     => $user->zip,
                'primary_phone'           => $user->getPrimaryPhone(),
                'home_phone'              => $user->getHomePhoneNumber(),
                'cell_phone'              => $user->getMobilePhoneNumber(),
                'status'                  => Enrollee::TO_CALL, // Setting STATUS here
                'requested_callback'      => null,
                'attempt_count'           => 0,
                'care_ambassador_user_id' => null,

                'source' => Enrollee::UNREACHABLE_PATIENT,
            ]
        );
    }
}
