<?php

namespace Tests\Unit;

use App\Notifications\CarePlanApprovalReminder;
use App\User;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CarePlanApprovalReminderTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_notification_was_sent()
    {
        //Set
        Notification::fake();

        $recipient = User::find(357);
        $numberOfCareplans = 10;

        //send notification
        $recipient->notify(new CarePlanApprovalReminder($numberOfCareplans));

        //assert set
        Notification::assertSentTo(
            $recipient,
            CarePlanApprovalReminder::class,
            function ($notification) use ($recipient, $numberOfCareplans) {
                $this->checkToMail($notification, $recipient, $numberOfCareplans);
                $this->checkToDatabase($notification, $recipient, $numberOfCareplans);

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
