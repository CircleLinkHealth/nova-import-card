<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Call;
use App\Contracts\DirectMail;
use App\Contracts\DirectMailableNotification;
use App\DirectMailMessage;
use App\Events\CarePlanWasApproved;
use App\Listeners\ChangeOrApproveCareplanResponseListener;
use App\Notifications\Channels\DirectMailChannel;
use App\Notifications\SendCarePlanForDirectMailApprovalNotification;
use App\Services\Calls\SchedulerService;
use App\Services\PhiMail\Events\DirectMailMessageReceived;
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
    
    public function test_it_sends_careplan_approval_dm_upon_qa_approval()
    {
        Notification::fake();
        $this->actingAs($this->administrator());
        
        $patient = $this->patient();
        $patient->setCarePlanStatus(CarePlan::DRAFT);
        $patient->setBillingProviderId($this->provider()->id);
        
        $this->assertEquals(CarePlan::DRAFT, $patient->carePlan->status);
        event(new CarePlanWasApproved($patient, $this->administrator()));
        $patient->carePlan->fresh();
        $this->assertEquals(CarePlan::QA_APPROVED, $patient->carePlan->status);
        
        Notification::assertSentTo(
            $this->provider(),
            SendCarePlanForDirectMailApprovalNotification::class,
            function (DirectMailableNotification $notification, $channels, $notifiable) use ($patient) {
                $this->assertContains(DirectMailChannel::class, $channels);
                $this->assertStringContainsString(
                    '#approve'.$patient->carePlan->id,
                    $notification->directMailBody($notifiable)
                );
                $this->assertStringContainsString(
                    '#change'.$patient->carePlan->id,
                    $notification->directMailBody($notifiable)
                );
                
                return true;
            }
        );
    }
    
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
    
    public function test_provider_can_approve_careplan_with_valid_dm_response()
    {
        $patient = $this->patient();
        $patient->setCarePlanStatus(CarePlan::QA_APPROVED);
        $this->assertEquals(CarePlan::QA_APPROVED, $patient->carePlan->status);
        $patient->setBillingProviderId($this->provider()->id);
        
        $this->provider()->emr_direct_address = 'drtest@upg.ssdirect.aprima.com'.str_random(5);
        
        $approvalCode = "#approve{$patient->carePlan->id}";
        $directMail   = factory(DirectMailMessage::class)->create(
            [
                'body' => "Yes, I approve $approvalCode",
                'from' => $this->provider()->emr_direct_address,
            ]
        );
        
        event(new DirectMailMessageReceived($directMail));
        
        $this->assertEquals(
            CarePlan::PROVIDER_APPROVED,
            $patient->carePlan->fresh()->status,
            "Careplan was not approved after DM with approval code was removed"
        );
    }
    
    public function tests_provider_can_login_with_passwordless_link()
    {
        $this->assertFalse(auth()->check());
        
        $notification = new SendCarePlanForDirectMailApprovalNotification($this->patient());
        
        $link = $notification->passwordlessLoginLink($this->provider());
        
        $response = $this->get($link)->assertStatus(302);
        
        $this->assertEquals($this->provider()->id, auth()->id());
    }
    
    public function test_extracting_approval_or_rejection_codes()
    {
        $listener = app(ChangeOrApproveCareplanResponseListener::class);
        
        $this->assertEquals(120, $listener->getCareplanIdToApprove('   #approve120'));
        $this->assertEquals(120, $listener->getCareplanIdToApprove('#approve120'));
        $this->assertEquals(32, $listener->getCareplanIdToApprove('#approve 32'));
        $this->assertEquals(3, $listener->getCareplanIdToApprove('#approve       3   '));
        $this->assertEquals(3, $listener->getCareplanIdToApprove('#      approve       3   '));
        
        $this->assertEquals(2, $listener->getCareplanIdToChange('#change2'));
        $this->assertEquals(312, $listener->getCareplanIdToChange('# change 312'));
        
    }
    
    public function test_provider_can_create_task_with_valid_dm_response()
    {
        $patient = $this->patient();
        $patient->setCarePlanStatus(CarePlan::QA_APPROVED);
        $this->assertEquals(CarePlan::QA_APPROVED, $patient->carePlan->status);
        $patient->setBillingProviderId($this->provider()->id);
        
        $this->provider()->emr_direct_address = 'drtest@upg.ssdirect.aprima.com'.str_random(5);
        
        $changeCode = "#change{$patient->carePlan->id}";
        $taskBody   = "Please make the following changes for this patient $changeCode";
        $directMail = factory(DirectMailMessage::class)->create(
            [
                'body' => $taskBody,
                'from' => $this->provider()->emr_direct_address,
            ]
        );
        
        event(new DirectMailMessageReceived($directMail));
        
        $this->assertDatabaseHas(
            'calls',
            [
                'type'            => 'task',
                'sub_type'        => SchedulerService::PROVIDER_REQUEST_FOR_CAREPLAN_APPROVAL_TYPE,
                'service'         => 'phone',
                'status'          => 'scheduled',
                'attempt_note'    => $taskBody,
                'scheduler'       => $this->provider()->id,
                'inbound_cpm_id'  => $patient->id,
                'outbound_cpm_id' => $patient->patientInfo->getNurse(),
                'asap'            => true,
            ]
        );
    }
}
