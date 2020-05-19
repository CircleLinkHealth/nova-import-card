<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Testing;

use CircleLinkHealth\Customer\Entities\SaasAccount;

class TestPatients extends CreatesTestPatients
{
    protected $noOfPatients = 5;

    /**
     * Data complements factory User (see ModelFactory).
     *
     * @return array
     */
    protected function data()
    {
        $patientData   = [];
        $saasAccountId = SaasAccount::firstOrFail()->id;
        for ($i = $this->noOfPatients; $i > 0; --$i) {
            $patientData[] = $this->getPatientFakeData($saasAccountId);
        }

        return $patientData;
    }

    private function getPatientFakeData(int $saasAccountId): array
    {
        return [
            'saas_account_id'   => $saasAccountId,
            'first_name'        => $this->faker->firstName,
            'last_name'         => $this->faker->lastName,
            'email'             => $this->faker->email,
            'username'          => $this->faker->email.now()->timestamp,
            'password'          => bcrypt('secret'),
            'is_auto_generated' => true,

            'program_id'          => $this->getPracticeId(),
            'billing_provider_id' => $this->getProvider()->id,

            //patient_info
            'gender'                     => 'M',
            'preferred_contact_language' => 'EN',
            'ccm_status'                 => 'enrolled',
            'birth_date'                 => '1945-11-27',
            'consent_date'               => '2019-03-13',
            'preferred_contact_timezone' => 'America/New_York',
            'mrn_number'                 => $this->faker->numberBetween(1, 10000),

            'conditions' => [
                'all',
            ],
            //number of dummy medications
            'medications' => 25,

            'address' => $this->faker->address,
            'city'    => $this->faker->city,
            'state'   => $this->faker->state,
            'zip'     => $this->faker->postcode,
        ];
    }
}
