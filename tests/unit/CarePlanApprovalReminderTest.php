<?php

namespace Tests\Unit;

use App\CarePlan;
use App\Notifications\CarePlanApprovalReminder;
use App\Patient;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\Helpers\CarePlanHelpers;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CarePlanApprovalReminderTest extends TestCase
{
    use CarePlanHelpers, DatabaseTransactions, UserHelpers;

    private $provider;
    private $patient;

    protected function setUp()
    {
        parent::setUp();
        
        $this->provider = $this->createUser(8, 'provider');
        $this->patient = $this->createUser(8, 'participant');

        $this->patient->billing_provider_id = $this->provider->id;
        $this->patient->ccm_status = Patient::ENROLLED;

        $this->assertEquals($this->provider->id, $this->patient->billing_provider_id);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_notification_was_sent()
    {
        //Set
        Notification::fake();

        $this->patient->care_plan_status = CarePlan::QA_APPROVED;
        
        $numberOfCareplans = CarePlan::getNumberOfCareplansPendingApproval($this->provider);

        //send notification
        $this->provider->sendCarePlanApprovalReminderEmail();

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

    public function checkToMail($notification, $recipient, $numberOfCareplans){
        $mailData = $notification->toMail($recipient)->build();

        $expectedTo = [['address' => $recipient->email, 'name' => $recipient->fullName]];

        $this->assertEquals("$numberOfCareplans CircleLink Care Plan(s) for your Approval!", $mailData->subject);
        $this->assertEquals($expectedTo, $mailData->to);
        $this->assertEquals('emails.careplansPendingApproval', $mailData->view);
    }

    public function checkToDatabase($notification, $recipient, $numberOfCareplans){

        $databaseData = $notification->toDatabase($recipient);

        $expected = ['numberOfCareplans' => $numberOfCareplans];

        $this->assertEquals($expected, $databaseData);
        $this->assertArrayHasKey('numberOfCareplans', $databaseData);
    }
}
