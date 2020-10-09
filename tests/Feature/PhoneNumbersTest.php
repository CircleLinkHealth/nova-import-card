<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use CircleLinkHealth\Customer\Entities\PhoneNumber;
use CircleLinkHealth\Customer\Entities\User;
use Tests\CustomerTestCase;

class PhoneNumbersTest extends CustomerTestCase
{
    const FAKE_CY_NUMBER          = '+35799992476';
    const FAKE_US_LANDLINE_NUMBER = '1800 975 709';
    const FAKE_US_MOBILE_NUMBER   = '+1-202-555-0118';
    const FAKE_US_MOBILE_NUMBER_2 = '+1-202-555-0119';
    const INVALID_US_NUMBER       = '+123456789';

    public function test_it_brings_any_number_for_mobile()
    {
        $patient = $this->createPatient(PhoneNumber::ALTERNATE, self::FAKE_US_MOBILE_NUMBER, 0);
        $number  = $patient->getPhoneNumberForSms();
        self::assertEquals(formatPhoneNumberE164(self::FAKE_US_MOBILE_NUMBER), $number);
    }

    public function test_it_brings_cypriot_number_for_mobile()
    {
        $patient = $this->createPatient(PhoneNumber::ALTERNATE, self::FAKE_CY_NUMBER, 0);
        self::assertEquals(self::FAKE_CY_NUMBER, $patient->getPhoneNumberForSms());
    }

    public function test_it_brings_mobile_number()
    {
        $patient = $this->createPatient(PhoneNumber::MOBILE, self::FAKE_US_MOBILE_NUMBER, 0);
        $this->addNumber($patient, PhoneNumber::ALTERNATE, self::FAKE_US_MOBILE_NUMBER_2, 0);
        self::assertEquals(formatPhoneNumberE164(self::FAKE_US_MOBILE_NUMBER), $patient->getPhoneNumberForSms());
    }

    public function test_it_brings_null_for_mobile()
    {
        $patient = $this->createPatient(PhoneNumber::MOBILE, self::INVALID_US_NUMBER, 0);
        self::assertEmpty($patient->getPhoneNumberForSms());
    }

    public function test_it_brings_primary_number_as_mobile()
    {
        $patient = $this->createPatient(PhoneNumber::MOBILE, self::FAKE_US_MOBILE_NUMBER, 0);
        $this->addNumber($patient, PhoneNumber::HOME, self::FAKE_US_MOBILE_NUMBER_2, 1);
        self::assertEquals(formatPhoneNumberE164(self::FAKE_US_MOBILE_NUMBER_2), $patient->getPhoneNumberForSms());
    }

    private function addNumber(User $patient, string $numberType, string $number, int $isPrimary)
    {
        $patient->phoneNumbers()->updateOrCreate(
            [
                'user_id' => $patient->id,
                'type'    => $numberType,
            ],
            [
                'number'     => $number,
                'is_primary' => $isPrimary,
            ]
        );

        return $patient;
    }

    private function createPatient(string $numberType, string $number, int $isPrimary)
    {
        $patient = $this->createUsersOfType('participant');
        $patient->phoneNumbers()->delete();

        return $this->addNumber($patient, $numberType, $number, $isPrimary);
    }
}
