<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\CarePlan;
use App\Notifications\CarePlanApprovalReminder;
use App\Patient;
use App\Practice;
use Illuminate\Support\Facades\Notification;
use Tests\Helpers\CarePlanHelpers;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;

class CarePlanApprovalReminderTest extends TestCase
{
    use CarePlanHelpers, UserHelpers;
    private $patient;
    private $practice;

    private $provider;

    protected function setUp()
    {
        parent::setUp();

        $this->practice = factory(Practice::class)->create();

        $this->provider = $this->createUser($this->practice->id, 'provider');
        $this->patient  = $this->createUser($this->practice->id, 'participant');

        $this->patient->setBillingProviderId($this->provider->id);
        $this->patient->setCcmStatus(Patient::ENROLLED);

        $this->assertEquals($this->provider->id, $this->patient->getBillingProviderId());
    }

    public function checkToDatabase($notification, $recipient, $numberOfCareplans)
    {
        $databaseData = $notification->toDatabase($recipient);

        $expected = ['numberOfCareplans' => $numberOfCareplans];

        $this->assertEquals($expected, $databaseData);
        $this->assertArrayHasKey('numberOfCareplans', $databaseData);
    }

    public function checkToMail($notification, $recipient, $numberOfCareplans)
    {
        $mailData = $notification->toMail($recipient)->build();

        $expectedTo = [['address' => $recipient->email, 'name' => $recipient->getFullName()]];

        $this->assertEquals("$numberOfCareplans CircleLink Care Plan(s) for your Approval!", $mailData->subject);
        $this->assertEquals($expectedTo, $mailData->to);
        $this->assertEquals('emails.careplansPendingApproval', $mailData->view);
    }

    /**
     * A basic test example.
     */
    public function test_notification_was_sent()
    {
        //Set
        Notification::fake();

        $this->patient->setCarePlanStatus(CarePlan::QA_APPROVED);

        $numberOfCareplans = 10;

        //send notification
        $this->provider->sendCarePlanApprovalReminder($numberOfCareplans);

        //assert set
        Notification::assertSentTo(
            $this->provider,
            CarePlanApprovalReminder::class,
            function ($notification) use ($numberOfCareplans) {
                $this->checkToMail($notification, $this->provider, $numberOfCareplans);
                $this->checkToDatabase($notification, $this->provider, $numberOfCareplans);

                return true;
            }
        );
    }
}
