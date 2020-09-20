<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use CircleLinkHealth\Eligibility\SelfEnrollment\Jobs\CreateSurveyOnlyUserFromEnrollee;
use Tests\TestCase;

class NewPatientEmailSanitizerTest extends TestCase
{
    public function test_it_creates_new_email_if_invalid()
    {
        self::assertFakeEmailCreated(1, '');
        self::assertFakeEmailCreated(1, null);
        self::assertFakeEmailCreated(1, CreateSurveyOnlyUserFromEnrollee::nullEmailValues()[0]);
        self::assertFakeEmailCreated(1, 'yo lo');
    }

    public function test_it_does_not_create_new_email()
    {
        self::assertFakeEmailNotCreated(1, 'test@example.com');
        self::assertFakeEmailNotCreated(1, 'test @example.com');
    }

    private static function assertFakeEmailCreated(int $id, ?string $email)
    {
        self::assertTrue(
            CreateSurveyOnlyUserFromEnrollee::fakeCpmFillerEmail($id) === CreateSurveyOnlyUserFromEnrollee::sanitizeEmail($id, $email),
            'Failed asserting that an email was created'
        );
    }

    private static function assertFakeEmailNotCreated(int $id, ?string $email)
    {
        self::assertTrue(
            str_replace(' ', '', $email) === CreateSurveyOnlyUserFromEnrollee::sanitizeEmail($id, $email),
            'Failed asserting that an email was not created'
        );
    }
}
