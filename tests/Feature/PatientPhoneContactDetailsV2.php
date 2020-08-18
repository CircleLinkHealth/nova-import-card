<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use CircleLinkHealth\Customer\Entities\PhoneNumber;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PatientPhoneContactDetailsV2 extends TestCase
{
    use UserHelpers;

    const LOCATION_ID    = 2;
    const PHONE_NUMBER_1 = '5417543019';
    const PHONE_NUMBER_2 = '5417543020';

    public function test_it_marks_phone_as_primary()
    {
        $practice = Practice::firstOrFail();
        $auth     = $this->createUser($practice->id);
        Auth::login($auth);
        $patient = $this->createUser($practice, 'participant');

        $phone = $this->createTestPhoneNumberForPatient($patient->id, self::PHONE_NUMBER_1);

        $response = $this->callMarkAsPrimaryEndpoint($patient->id, $phone);

        $response->assertStatus(200);
    }

    public function test_it_saves_new_alternate_phone_number()
    {
        $practice = Practice::firstOrFail();
        $auth     = $this->createUser($practice->id);
        $patient  = $this->createUser($practice, 'participant');
        Auth::login($auth);

        $response = $this->withHeaders([
            'X-Header' => 'Value',
        ])->json(
            'POST',
            route('patient.alternate.phone.create'),
            [
                'agentName'         => 'John',
                'agentEmail'        => 'john@example.com',
                'phoneNumber'       => self::PHONE_NUMBER_1,
                'agentRelationship' => 'Cousin',
                'patientUserId'     => $patient->id,
            ]
        );

        $response->assertStatus(200);
    }

    public function test_it_saves_new_phone_number()
    {
        $practice = Practice::firstOrFail();
        $auth     = $this->createUser($practice->id);
        $patient  = $this->createUser($practice, 'participant');
        Auth::login($auth);

        $response = $this->withHeaders([
            'X-Header' => 'Value',
        ])->json(
            'POST',
            route('patient.phone.create'),
            [
                'phoneType'     => PhoneNumber::MOBILE,
                'patientUserId' => $patient->id,
                'phoneNumber'   => self::PHONE_NUMBER_1,
                'makePrimary'   => false,
            ]
        );

        $response->assertStatus(200);
    }

    public function test_only_one_primary_phone_exists()
    {
        $practice = Practice::firstOrFail();
        $auth     = $this->createUser($practice->id);
        Auth::login($auth);
        $patient = $this->createUser($practice, 'participant');

        $phone    = $this->createTestPhoneNumberForPatient($patient->id, self::PHONE_NUMBER_1);
        $response = $this->callMarkAsPrimaryEndpoint($patient->id, $phone);
        $response->assertStatus(200);
        $patientPrimaryPhones = $this->getTestPatientPrimaryPhones($patient);
        self::assertTrue(1 === $patientPrimaryPhones->count());
        self::assertTrue(self::PHONE_NUMBER_1 === $patientPrimaryPhones->first()->number);

        $phone2   = $this->createTestPhoneNumberForPatient($patient->id, self::PHONE_NUMBER_2);
        $response = $this->callMarkAsPrimaryEndpoint($patient->id, $phone2);
        $response->assertStatus(200);
        $patientPrimaryPhones = $this->getTestPatientPrimaryPhones($patient);
        self::assertTrue(1 === $patientPrimaryPhones->count());
        self::assertTrue(self::PHONE_NUMBER_2 === $patientPrimaryPhones->first()->number);
    }

    private function callMarkAsPrimaryEndpoint(int $id, PhoneNumber $phone)
    {
        return $this->withHeaders([
            'X-Header' => 'Value',
        ])->json(
            'POST',
            route('primary.phone.mark'),
            [
                'patientUserId' => $id,
                'phoneId'       => $phone,
            ]
        );
    }

    private function createTestPhoneNumberForPatient(int $id, string $number)
    {
        return  PhoneNumber::create([
            'user_id'     => $id,
            'number'      => $number,
            'type'        => strtolower(PhoneNumber::HOME),
            'is_primary'  => true,
            'location_id' => self::LOCATION_ID,
        ]);
    }

    private function getTestPatientPrimaryPhones(\CircleLinkHealth\Customer\Entities\User $patient)
    {
        return $patient->load('phoneNumbers')
            ->phoneNumbers()
            ->where('is_primary', true);
    }
}
