<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\CarePlan;
use App\Notifications\CarePlanApprovalReminder;
use App\Services\PhiMail\IncomingMessageHandler;
use App\Services\PhiMail\PhiMail;
use App\ValueObjects\SimpleNotification;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Support\Facades\Notification;
use Tests\Helpers\CarePlanHelpers;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;

class CarePlanApprovalReminderTest extends TestCase
{
    use CarePlanHelpers;
    use UserHelpers;

    const CLH_TEST_DM_ADDRESS = 'circlelinkhealth@test.directproject.net';

    private $directMail;
    private $patient;
    private $practice;

    private $provider;

    protected function setUp()
    {
        parent::setUp();

        $this->directMail = new PhiMail(
            app()->make(IncomingMessageHandler::class)
        );

        $this->practice = factory(Practice::class)->create();

        $this->provider = $this->createUser($this->practice->id, 'provider');
        $this->patient  = $this->createUser($this->practice->id, 'participant');

        $this->patient->setBillingProviderId($this->provider->id);
        $this->patient->setCcmStatus(Patient::ENROLLED);

        $this->assertEquals($this->provider->id, $this->patient->getBillingProviderId());
    }

    /**
     * @param $notification
     * @param $recipient
     * @param $numberOfCareplans
     */
    public function checkToDatabase($notification, $recipient, $numberOfCareplans)
    {
        $databaseData = $notification->toDatabase($recipient);

        $this->assertArrayHasKey('numberOfCareplans', $databaseData);
        $this->assertEquals($databaseData['numberOfCareplans'], $numberOfCareplans);
    }

    public function checkToDirectMail($notification, $recipient)
    {
        $dmNotification = $notification->toDirectMail($recipient);

        $this->assertInstanceOf(SimpleNotification::class, $dmNotification);
        $data = $dmNotification->toArray();
        $this->assertArrayHasKey('body', $data);
        $this->assertArrayHasKey('subject', $data);

        //replicate DirectMailChannel
        $result = $this->directMail->send(
            $recipient->emr_direct_address,
            null,
            null,
            null,
            null,
            $data['body'],
            $data['subject']
        );

        foreach ($result as $sent) {
            $this->assertTrue($sent->succeeded);
            $this->assertEquals(self::CLH_TEST_DM_ADDRESS, $sent->recipient);
            $this->assertNull($sent->errorText);
        }
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
     * This test is needed because the CarePlanApprovalReminder Notification checks for practice->cpmSettings->dm_careplan_approval_reminders,
     * to determine if the notification will be sent via Mail or via DirectMail.
     */
//    public function test_direct_mail_notification_was_sent()
//    {
//        //Set
//        Notification::fake();
//
//        $this->patient->setCarePlanStatus(CarePlan::QA_APPROVED);
//        $numberOfCareplans                  = 10;
//        $this->provider->emr_direct_address = 'circlelinkhealth@test.directproject.net';
//        $this->provider->save();
//
//        $this->provider->primaryPractice->setDirectMailCareplanApprovalReminders(1);
//
//        //send notification
//        $this->provider->sendCarePlanApprovalReminder($numberOfCareplans);
//
//        //assert set
//        Notification::assertSentTo(
//            $this->provider,
//            CarePlanApprovalReminder::class,
//            function ($notification) use ($numberOfCareplans) {
//                $this->checkToDirectMail($notification, $this->provider);
//
//                return true;
//            }
//        );
//    }

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
