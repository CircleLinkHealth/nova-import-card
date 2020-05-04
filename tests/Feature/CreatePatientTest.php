<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\User;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use Tests\Helpers\PatientHelpers;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;

class CreatePatientTest extends TestCase
{
    use PatientHelpers;
    use UserHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setAdminUser();
    }

    /**
     * Test that we can create a patient along
     * with a new provider user.
     */
    public function test_create_patient_with_new_provider()
    {
        /** @var User $providerUser */
        $providerUser = factory(User::class)->make();

        /** @var Practice $practice */
        $practice = factory(Practice::class)->create();

        /** @var User $patient */
        $patient = factory(User::class)->make();

        $response = $this->json('POST', '/manage-patients/store', [
            'patient' => [
                'firstName'   => $patient->first_name,
                'lastName'    => $patient->last_name,
                'dob'         => Carbon::now()->year(1970)->toISOString(),
                'phoneNumber' => '+1234567890',
                'email'       => $patient->email,
            ],
            'provider' => [
                'firstName'         => $providerUser->first_name,
                'lastName'          => $providerUser->last_name,
                'email'             => $providerUser->email,
                'primaryPracticeId' => $practice->id,
                'suffix'            => 'MD',
                'specialty'         => 'Anesthesiology',
                'isClinical'        => 1,
            ],
        ]);

        $this->assertTrue(200 === $response->status(), $response->content());

        $this->assertTrue(
            1 === User::whereEmail($patient->email)->count(),
            'patient with this email should be exactly one'
        );

        $this->assertTrue(
            1 === User::whereEmail($providerUser->email)->count(),
            'provider with this email should be exactly one'
        );

        $createdUser     = User::whereEmail($patient->email)->first();
        $createdProvider = User::whereEmail($providerUser->email)->first();
        $this->assertTrue($createdUser->billingProviderUser()->id === $createdProvider->id);
        $this->assertTrue(1 === $createdUser->patientInfo->is_awv, 'user must have is_awv = true');
    }

    /**
     * Test that we can create a patient with an existing provider
     * Test that created patient has is_awv = true.
     */
    public function test_create_patient_with_provider_id()
    {
        $this->createPatient();
    }

    /**
     * Become an admin user for the session.
     */
    private function setAdminUser()
    {
        $this->be($this->createAdminUser());
    }
}
