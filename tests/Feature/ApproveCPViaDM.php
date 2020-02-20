<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Contracts\DirectMail;
use App\Contracts\DirectMailableNotification;
use App\DirectMailMessage;
use App\Events\CarePlanWasApproved;
use App\Notifications\Channels\DirectMailChannel;
use App\Notifications\SendCarePlanForDirectMailApprovalNotification;
use CircleLinkHealth\Core\Facades\Notification;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Event;
use Tests\CustomerTestCase;

class ApproveCPViaDM extends CustomerTestCase
{
    public function directMailSubject($patient): string
    {
        return "{$patient->getFullName()}'s CCM Care Plan to approve!";
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_cp_approve_notification_is_sent_via_dm()
    {
        $this->patient()->carePlan->status = CarePlan::QA_APPROVED;
        $this->patient()->carePlan->save();

        $this->provider()->emr_direct_address = 'circlelinkhealth@test.directproject.net';

        $notification = new SendCarePlanForDirectMailApprovalNotification($this->patient());
        $this->provider()->notify($notification);

        $this->assertDatabaseHas(
            (new DirectMailMessage())->getTable(),
            [
                'from'       => config('services.emr-direct.user'),
                'to'         => 'circlelinkhealth@test.directproject.net',
                'subject'    => $this->directMailSubject($this->patient()),
                'status'     => DirectMailMessage::STATUS_SUCCESS,
                'direction'  => DirectMailMessage::DIRECTION_SENT,
                'error_text' => null,
            ]
        );
    }

    public function test_it_sends_careplan_approval_dm_upon_qa_approval()
    {
        Notification::fake();
        $this->actingAs($this->administrator());

        $patient = $this->patient();
        $patient->setCarePlanStatus(CarePlan::DRAFT);
        $patient->setBillingProviderId($this->provider()->id);

        $this->assertEquals(CarePlan::DRAFT, $patient->carePlan->status);
        event(new CarePlanWasApproved($patient));
        $this->assertEquals(CarePlan::QA_APPROVED, $patient->carePlan->status);

        Notification::assertSentTo(
            $this->provider(),
            SendCarePlanForDirectMailApprovalNotification::class,
            function (DirectMailableNotification $notification, $channels, $notifiable) use ($patient) {
                $this->assertContains(DirectMailChannel::class, $channels);
                $this->assertStringContainsString('#approve'.$patient->carePlan->id, $notification->directMailBody($notifiable));
                $this->assertStringContainsString('#change'.$patient->carePlan->id, $notification->directMailBody($notifiable));
                
                return true;
            }
        );
    }
    
    public function tests_provider_can_login_with_passwordless_link() {
        $this->assertFalse(auth()->check());
        
        $notification = new SendCarePlanForDirectMailApprovalNotification($this->patient());
        
        $link = $notification->passwordlessLoginLink($this->provider());
        
        $response = $this->get($link)->assertStatus(302);
        
        $this->assertEquals($this->provider()->id, auth()->id());
    }
    
    public function test_provider_can_approve_careplan_with_valid_dm_response() {
    
    }

    public function test_receive_dm()
    {
        $dm = app(DirectMail::class);

        $dm->receive();
    }
}
