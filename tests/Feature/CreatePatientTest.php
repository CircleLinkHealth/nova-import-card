<?php

namespace Tests\Feature;

use App\User;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use Tests\Helpers\PatientHelpers;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;

class CreatePatientTest extends TestCase
{
    use UserHelpers;
    use PatientHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setAdminUser();
    }

    /**
     * Test that we can create a patient with an existing provider
     * Test that created patient has is_awv = true.
     */
    public function testCreatePatientWithProviderId()
    {
        $this->createPatient();
    }

    /**
     * Test that we can create a patient along
     * with a new provider user.
     */
    public function testCreatePatientWithNewProvider()
    {
        /** @var User $providerUser */
        $providerUser = factory(User::class)->make();

        /** @var Practice $practice */
        $practice = factory(Practice::class)->create();

        /** @var User $patient */
        $patient = factory(User::class)->make();

        $response = $this->json('POST', '/manage-patients/store', [
            'patient'  => [
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

        $this->assertTrue($response->status() === 200, $response->content());

        $this->assertTrue(User::whereEmail($patient->email)->count() === 1,
            'patient with this email should be exactly one');

        $this->assertTrue(User::whereEmail($providerUser->email)->count() === 1,
            'provider with this email should be exactly one');

        $createdUser = User::whereEmail($patient->email)->first();
        $createdProvider = User::whereEmail($providerUser->email)->first();
        $this->assertTrue($createdUser->billingProviderUser()->id === $createdProvider->id);
        $this->assertTrue($createdUser->patientInfo->is_awv === 1, 'user must have is_awv = true');
    }

    /**
     * Become an admin user for the session.
     */
    private function setAdminUser()
    {
        $this->be($this->createAdminUser());
    }
}
