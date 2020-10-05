<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Traits\Tests\CareAmbassadorHelpers;
use CircleLinkHealth\Customer\Entities\PatientContactWindow;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Jobs\ImportConsentedEnrollees;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class ConsentedEnrolleeImportedTest extends TestCase
{
    use \CircleLinkHealth\Customer\Traits\UserHelpers;
    use CareAmbassadorHelpers;

    protected $careAmbassadorUser;
    protected $enrollee;
    protected $practice;
    protected $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->practice           = factory(Practice::class)->create();
        $this->careAmbassadorUser = $this->createUser($this->practice->id, 'care-ambassador');
        $this->provider           = $this->createUser($this->practice->id, 'provider');
        $this->enrollee           = factory(Enrollee::class)->create();
        $this->createEligibilityJobDataForEnrollee($this->enrollee);
    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_consented_enrollee_importing()
    {
        auth()->login($this->careAmbassadorUser);

        //Care Ambassador will confirm-edit enrollee info on CA Panel consented modal
        //These changes should carry over to the newly imported User.
        $otherNote = 'Test this goes to patient info';
        $email     = 'test-this-email-gets-updated@test-email.com';
        $address   = 'Test that this address gets updated';
        $address2  = 'Test that this address gets updated 2';
        $zip       = 66646;
        $city      = 'Test this city gets updated';
        $this->performActionOnEnrollee($this->enrollee, Enrollee::CONSENTED, [
            'extra'     => $otherNote,
            'email'     => $email,
            'address'   => $address,
            'address_2' => $address2,
            'zip'       => $zip,
            'city'      => $city,
            'times'     => ['09:00-18:00'],
        ]);

        $enrollee = $this->enrollee->fresh();

        $this->assertNotNull($enrollee->user);
        $this->assertNotNull($enrollee->user->patientInfo);
        $this->assertTrue($enrollee->user->patientInfo->general_comment === $otherNote);
        $this->assertTrue($enrollee->user->email === $email);
        $this->assertTrue($enrollee->user->address == $address);
        $this->assertTrue($enrollee->user->address2 == $address2);
        $this->assertTrue($enrollee->user->city == $city);
        $this->assertTrue($enrollee->user->zip == $zip);

        $patientContactWindow = PatientContactWindow::wherePatientInfoId($enrollee->user->patientInfo->id)->first();
        $this->assertTrue('09:00:00' === $patientContactWindow->window_time_start);
        $this->assertTrue('18:00:00' === $patientContactWindow->window_time_end);
    }

    public function test_enrollee_is_imported_according_to_user_role()
    {
        auth()->login($this->careAmbassadorUser);

        Bus::fake();

        $participantUser = $this->createUser($this->practice->id, 'participant');

        $participantEnrollee = factory(Enrollee::class)->create([
            'user_id' => $participantUser->id,
        ]);

        $this->performActionOnEnrollee($participantEnrollee, Enrollee::CONSENTED);

        Bus::assertNotDispatched(ImportConsentedEnrollees::class);

        $surveyOnlyUser = $this->createUser($this->practice->id, 'survey-only');

        $surveyOnlyEnrollee = factory(Enrollee::class)->create([
            'user_id' => $surveyOnlyUser->id,
        ]);

        $this->performActionOnEnrollee($surveyOnlyEnrollee, Enrollee::CONSENTED);

        Bus::assertDispatched(ImportConsentedEnrollees::class);
    }
}
