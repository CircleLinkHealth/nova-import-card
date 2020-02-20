<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\CarePlan;
use App\Contracts\DirectMail;
use App\DirectMailMessage;
use App\Notifications\SendCarePlanForDirectMailApprovalNotification;
use Tests\CustomerTestCase;

class ApproveCPViaDM extends CustomerTestCase
{
    public function directMailSubject($patient): string
    {
        return "{$patient->getFullName()}'s CCM Care Plan to approve!";
    }
    
    public function test_it_sends_careplan_approval_dm_upon_qa_approval () {
        Event::fake();
    
        // Perform order shipping...
    
        Event::assertDispatched(OrderShipped::class, function ($e) use ($order) {
            return $e->order->id === $order->id;
        });
    
        // Assert an event was dispatched twice...
        Event::assertDispatched(OrderShipped::class, 2);
    
        // Assert an event was not dispatched...
        Event::assertNotDispatched(OrderFailedToShip::class);
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

        $this->assertDatabaseHas((new DirectMailMessage())->getTable(), [
            'from'       => config('services.emr-direct.user'),
            'to'         => 'circlelinkhealth@test.directproject.net',
            'subject'    => $this->directMailSubject($this->patient()),
            'status'     => DirectMailMessage::STATUS_SUCCESS,
            'direction'  => DirectMailMessage::DIRECTION_SENT,
            'error_text' => null,
        ]);
    }

    public function test_receive_dm()
    {
        $dm = app(DirectMail::class);

        $dm->receive();
    }
}
