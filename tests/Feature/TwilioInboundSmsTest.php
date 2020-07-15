<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Call;
use App\Notifications\PatientUnsuccessfulCallReplyNotification;
use App\Services\Calls\SchedulerService;
use CircleLinkHealth\Core\Facades\Notification;
use CircleLinkHealth\Customer\Entities\PatientNurse;
use Tests\Concerns\TwilioFake\Twilio;
use Tests\CustomerTestCase;

class TwilioInboundSmsTest extends CustomerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Twilio::fake();

        $nurse   = $this->careCoach();
        $patient = $this->patient();
        PatientNurse::updateOrCreate(
            ['patient_user_id' => $patient->id],
            [
                'nurse_user_id'           => $nurse->id,
                'temporary_nurse_user_id' => null,
                'temporary_from'          => null,
                'temporary_to'            => null,
            ]
        );
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_should_create_asap_call_to_nurse()
    {
        $patient  = $this->patient();
        $data     = $this->getRequestData($patient->getPhoneNumberForSms(), 'test');
        $response = $this->post(route('twilio.sms.inbound'), $data);
        $response->assertStatus(200);

        /** @var Call $call */
        $call = Call::whereInboundCpmId($patient->id)
            ->where('type', '=', SchedulerService::TASK_TYPE)
            ->where('sub_type', '=', SchedulerService::SCHEDULE_NEXT_CALL_PER_PATIENT_SMS)
            ->first();
        self::assertNotNull($call);
        self::assertEquals(1, $call->asap);
        self::assertEquals('test', $call->attempt_note);

        Notification::assertSentTo($patient, PatientUnsuccessfulCallReplyNotification::class);
    }

    public function test_should_not_create_more_than_one_asap_task_with_multiple_sms()
    {
        $patient  = $this->patient();
        $data     = $this->getRequestData($patient->getPhoneNumberForSms(), 'test');
        $response = $this->post(route('twilio.sms.inbound'), $data);
        $response->assertStatus(200);

        $data     = $this->getRequestData($patient->getPhoneNumberForSms(), 'test2');
        $response = $this->post(route('twilio.sms.inbound'), $data);
        $response->assertStatus(200);

        /** @var Call $call */
        $call = Call::whereInboundCpmId($patient->id)
            ->where('type', '=', SchedulerService::TASK_TYPE)
            ->where('sub_type', '=', SchedulerService::SCHEDULE_NEXT_CALL_PER_PATIENT_SMS)
            ->first();
        self::assertNotNull($call);
        self::assertEquals(1, $call->asap);
        self::assertEquals("test\ntest2", $call->attempt_note);

        Notification::assertSentTo($patient, PatientUnsuccessfulCallReplyNotification::class);
    }

    private function getRequestData(string $fromPhoneNumber, string $body)
    {
        return [
            'ToCountry'     => 'US',
            'ToState'       => 'FL',
            'SmsMessageSid' => 'SM49ea7c4fbf8971700230244bb91b7a85',
            'NumMedia'      => '0',
            'ToCity'        => null,
            'FromZip'       => null,
            'SmsSid'        => 'SM49ea7c4fbf8971700230244bb91b7a85',
            'FromState'     => null,
            'SmsStatus'     => 'received',
            'FromCity'      => null,
            'Body'          => $body,
            'FromCountry'   => 'CY',
            'To'            => '+18634171503',
            'ToZip'         => null,
            'NumSegments'   => '1',
            'MessageSid'    => 'SM49ea7c4fbf8971700230244bb91b7a85',
            'AccountSid'    => 'ACbb32b6c7356311495b757fb29c15df82',
            'From'          => $fromPhoneNumber,
            'ApiVersion'    => '2010-04-01',
        ];
    }
}
