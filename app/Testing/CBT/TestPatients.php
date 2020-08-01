<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Testing\CBT;

use App\Testing\CreatesTestPatients;

class TestPatients extends CreatesTestPatients
{
    protected function data()
    {
        return [
            'patient_1' => [
                //user
                'first_name'        => 'CBT',
                'last_name'         => 'Automation 1',
                'email'             => 'cbtAutomation1@test.com',
                'password'          => bcrypt('secret'),
                'is_auto_generated' => true,

                'program_id'          => $this->getPracticeId(),
                'billing_provider_id' => $this->getProvider(13242)->id,

                //patient_info
                'gender'                     => 'M',
                'preferred_contact_language' => 'EN',
                'ccm_status'                 => 'enrolled',
                'birth_date'                 => '1945-11-27',
                'consent_date'               => '2019-03-13',
                'preferred_contact_timezone' => 'America/New_York',
                'mrn_number'                 => 236025386923,

                'conditions' => [
                    'all',
                ],
                //number of dummy medications
                'medications' => 25,
            ],
            'patient_2' => [
                //user
                'first_name' => 'CBT',
                'last_name'  => 'Automation 2',
                'email'      => 'cbtAutomation2@test.com',

                'program_id'          => $this->getPracticeId(),
                'billing_provider_id' => $this->getProvider(13242)->id,

                //patient_info
                'gender'                     => 'F',
                'preferred_contact_language' => 'EN',
                'ccm_status'                 => 'enrolled',
                'birth_date'                 => '1927-12-07',
                'consent_date'               => '2018-05-27',
                'preferred_contact_timezone' => 'America/New_York',
                'mrn_number'                 => 186027387923,

                'conditions' => [
                    'Hypertension',
                    'Dementia',
                    'Diabetes Type 2',
                ],
                //number of dummy medications
                'medications' => 0,
            ],
        ];
    }
}
