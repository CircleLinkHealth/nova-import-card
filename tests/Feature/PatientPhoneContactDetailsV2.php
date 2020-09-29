<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use CircleLinkHealth\Customer\Entities\PhoneNumber;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Traits\UserHelpers;
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

        $this->actingAs($auth);

        $patient = $this->createUser($practice, 'participant');

        $phone = $this->createTestPhoneNumberForPatient($patient->id, self::PHONE_NUMBER_1);

        $response = $this->callMarkAsPrimaryEndpoint($patient->id, $phone->id);

        $response->assertStatus(200);

        $this->assertDatabaseHas('phone_numbers', [
            'user_id'     => $patient->id,
            'number'      => $phone->number,
            'type'        => $phone->type,
            'is_primary'  => $phone->fresh()->is_primary,
            'location_id' => $phone->location_id,
        ]);
    }

    public function test_it_saves_new_alternate_phone_number()
    {
        $practice = Practice::firstOrFail();
        $auth     = $this->createUser($practice->id);
        $patient  = $this->createUser($practice, 'participant');
        $this->actingAs($auth);

        $response = $this->json(
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
        $this->assertDatabaseHas('patient_info', [
            'agent_name'         => 'John',
            'agent_email'        => 'john@example.com',
            'agent_telephone'    => formatPhoneNumberE164(self::PHONE_NUMBER_1),
            'agent_relationship' => 'Cousin',
            'user_id'            => $patient->id,
        ]);
    }

    public function test_it_saves_new_phone_number()
    {
        $practice = Practice::firstOrFail();
        $auth     = $this->createUser($practice->id);
        $patient  = $this->createUser($practice, 'participant');
        $this->actingAs($auth);

        $response = $this->json(
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
        $this->assertDatabaseHas('phone_numbers', [
            'type'       => PhoneNumber::MOBILE,
            'user_id'    => $patient->id,
            'number'     => formatPhoneNumberE164(self::PHONE_NUMBER_1),
            'is_primary' => false,
        ]);
    }

    public function test_only_one_primary_phone_exists()
    {
        $practice = Practice::firstOrFail();
        $auth     = $this->createUser($practice->id);
        $this->actingAs($auth);
        $patient = $this->createUser($practice, 'participant')->load('phoneNumbers');

        $phone = $this->createTestPhoneNumberForPatient($patient->id, self::PHONE_NUMBER_1);

        self::assertTrue( ! $phone->is_primary);

        $response = $this->callMarkAsPrimaryEndpoint($patient->id, $phone->id);
        $response->assertStatus(200);
        $patientPrimaryPhone = $this->getTestPatientPrimaryPhones($patient, $phone->id);

        self::assertTrue($patientPrimaryPhone->exists());
        self::assertTrue(self::PHONE_NUMBER_1 === $patientPrimaryPhone->number);

        $this->assertDatabaseHas('phone_numbers', [
            'user_id'     => $patient->id,
            'number'      => $phone->number,
            'type'        => $phone->type,
            'is_primary'  => $phone->fresh()->is_primary,
            'location_id' => $phone->location_id,
        ]);

        $phone2   = $this->createTestPhoneNumberForPatient($patient->id, self::PHONE_NUMBER_2);
        $response = $this->callMarkAsPrimaryEndpoint($patient->id, $phone2->id);
        $response->assertStatus(200);
        $patientPrimaryPhone2 = $this->getTestPatientPrimaryPhones($patient, $phone2->id);

        self::assertTrue($patientPrimaryPhone2->exists());
        self::assertTrue(self::PHONE_NUMBER_2 === $patientPrimaryPhone2->number);

        $this->assertDatabaseHas('phone_numbers', [
            'user_id'     => $patient->id,
            'number'      => $phone->number,
            'type'        => $phone->type,
            'is_primary'  => false,
            'location_id' => $phone->location_id,
        ]);

        $this->assertDatabaseHas('phone_numbers', [
            'user_id'     => $patient->id,
            'number'      => $phone2->number,
            'type'        => $phone2->type,
            'is_primary'  => $phone2->fresh()->is_primary,
            'location_id' => $phone2->location_id,
        ]);
    }

    private function callMarkAsPrimaryEndpoint(int $userId, int $phoneId)
    {
        return $this->json(
            'POST',
            route('primary.phone.mark'),
            [
                'patientUserId' => $userId,
                'phoneId'       => $phoneId,
            ]
        );
    }

    private function createTestPhoneNumberForPatient(int $id, string $number)
    {
        return PhoneNumber::create([
            'user_id'     => $id,
            'number'      => $number,
            'type'        => strtolower(PhoneNumber::HOME),
            'is_primary'  => false,
            'location_id' => self::LOCATION_ID,
        ]);
    }

    private function getTestPatientPrimaryPhones(\CircleLinkHealth\Customer\Entities\User $patient, int $phoneId)
    {
        return $patient->phoneNumbers()
            ->where('is_primary', '=', true)
            ->where('id', '=', $phoneId)
            ->first();
    }
}
