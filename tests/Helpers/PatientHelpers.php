<?php


namespace Tests\Helpers;


use App\User;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;

trait PatientHelpers
{
    /**
     * Create an AWV Patient.
     * Will have to call as an admin user ($this->be($adminUser))
     */
    public function createPatient(): User
    {
        /** @var User $providerUser */
        $providerUser = factory(User::class)->create();

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
                'id'                => $providerUser->id,
                'primaryPracticeId' => $practice->id,
            ],
        ]);

        $this->assertTrue($response->status() === 200, $response->content());

        $this->assertTrue(User::whereEmail($patient->email)->count() === 1,
            'patient with this email should be exactly one');

        $createdUser = User::whereEmail($patient->email)->first();
        $this->assertTrue($createdUser->billingProviderUser()->id === $providerUser->id);
        $this->assertTrue($createdUser->patientInfo->is_awv === 1, 'user must have is_awv = true');

        return $createdUser;
    }

}
