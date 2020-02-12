<?php
/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Testing;

class TestPatients extends CreatesTestPatients
{
    protected $noOfPatients = 5;

    /**
     * Data complements factory User (see ModelFactory)
     *
     * @return array
     */
    protected function data()
    {

        $patientData = [];
        for ($i = $this->noOfPatients; $i > 0; $i--) {
            $patientData[] = $this->getPatientFakeData();
        }

        return $patientData;
    }

    private function getPatientFakeData(): array
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name'  => $this->faker->lastName,
            'email'      => $this->faker->email,
            'password'                   => bcrypt('secret'),
            'is_auto_generated'          => true,

            'program_id'                 => $this->getPracticeId(),
            'billing_provider_id'        => $this->getProvider()->id,

            //patient_info
            'gender'                     => 'M',
            'preferred_contact_language' => 'EN',
            'ccm_status'                 => 'enrolled',
            'birth_date'                 => '1945-11-27',
            'consent_date'               => '2019-03-13',
            'preferred_contact_timezone' => 'America/New_York',
            'mrn_number'                 => $this->faker->numberBetween(1, 10000),

            'conditions'  => [
                'all',
            ],
            //number of dummy medications
            'medications' => 25,
        ];
    }
}