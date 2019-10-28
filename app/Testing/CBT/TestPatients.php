<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Testing\CBT;

class TestPatients
{
    /**
     * To create more patients, simply add to the array below, following the existing array structure.
     */
    const CBT_TEST_PATIENTS = [
        'patient_1' => [
            //user
            'first_name' => 'CBT',
            'last_name'  => 'Automation 1',
            'email'      => 'cbtAutomation1@test.com',
            //use name to get practice id for 'program_id'
            'practice_name'       => 'demo',
            'billing_provider_id' => 13242,

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
            //use name to get practice id for 'program_id'
            'practice_name'       => 'demo',
            'billing_provider_id' => 13242,

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

    /**
     * @param int $i
     *
     * Generate dummy medication names for users
     *
     * @return array
     */
    public static function testMedications($i = 25)
    {
        $medications = [];
        while ($i > 0) {
            $medications[] = ['name' => 'med'.' '.$i];
            --$i;
        }

        return $medications;
    }
}
