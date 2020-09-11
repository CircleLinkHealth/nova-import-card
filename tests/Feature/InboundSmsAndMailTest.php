<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Algorithms\Calls\NurseFinder\NurseFinderEloquentRepository;
use App\Call;
use App\Notifications\PatientUnsuccessfulCallNotification;
use App\Notifications\PatientUnsuccessfulCallReplyNotification;
use App\Services\Calls\SchedulerService;
use CircleLinkHealth\Core\Entities\DatabaseNotification;
use CircleLinkHealth\Core\Facades\Notification;
use CircleLinkHealth\Customer\Entities\PhoneNumber;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\Concerns\TwilioFake\Twilio;
use Tests\CustomerTestCase;

class InboundSmsAndMailTest extends CustomerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Mail::fake();
        Twilio::fake();

        $nurse   = $this->careCoach();
        $patient = $this->patient();

        app(NurseFinderEloquentRepository::class)->assign($patient->id, $nurse->id);

        //need to have an entry in db to actually handle inbound sms/mail
        $this->addDbEntryForNotification($patient);
    }

    public function test_should_create_asap_call_to_nurse()
    {
        $patient  = $this->patient();
        $data     = $this->getSmsRequestData($patient->getPhoneNumberForSms(), 'test');
        $response = $this->post(route('twilio.sms.inbound'), $data);
        $response->assertStatus(200);

        /** @var Call $call */
        $call = Call::whereInboundCpmId($patient->id)
            ->where('type', '=', SchedulerService::TASK_TYPE)
            ->where('sub_type', '=', SchedulerService::SCHEDULE_NEXT_CALL_PER_PATIENT_SMS)
            ->first();
        self::assertNotNull($call);
        self::assertEquals(1, $call->asap);
        self::assertStringContainsString('test', $call->attempt_note);

        Notification::assertSentTo($patient, PatientUnsuccessfulCallReplyNotification::class);
    }

    public function test_should_create_asap_call_to_nurse_from_email_that_belongs_to_two_patients()
    {
        /** @var Collection $patients */
        $aPatient        = $this->createUsersOfType('participant', 1);
        $aPatient->email = 'test_should_create_asap_call_to_nurse_from_email_that_belongs_to_two_patients@example.org';
        $aPatient->save();
        $bPatient        = $this->patient();
        $bPatient->email = 'test_should_create_asap_call_to_nurse_from_email_that_belongs_to_two_patients+family@example.org';
        $bPatient->save();

        $patients = collect([$aPatient, $bPatient]);
        $patient  = $patients->last();

        $data     = $this->getMailRequestData($patient->email, 'test');
        $response = $this->post(route('postmark.inbound'), $data);
        $response->assertStatus(200);

        /** @var Call $call */
        $call = Call::whereInboundCpmId($patient->id)
            ->where('type', '=', SchedulerService::TASK_TYPE)
            ->where('sub_type', '=', SchedulerService::SCHEDULE_NEXT_CALL_PER_PATIENT_SMS)
            ->first();
        self::assertNotNull($call);
        self::assertEquals(1, $call->asap);
        self::assertStringContainsString('test', $call->attempt_note);

        Notification::assertSentTo($patient, PatientUnsuccessfulCallReplyNotification::class);
    }

    public function test_should_create_asap_call_to_nurse_from_inbound_mail()
    {
        $patient  = $this->patient();
        $data     = $this->getMailRequestData($patient->email, 'test');
        $response = $this->post(route('postmark.inbound'), $data);
        $response->assertStatus(200);

        /** @var Call $call */
        $call = Call::whereInboundCpmId($patient->id)
            ->where('type', '=', SchedulerService::TASK_TYPE)
            ->where('sub_type', '=', SchedulerService::SCHEDULE_NEXT_CALL_PER_PATIENT_SMS)
            ->first();
        self::assertNotNull($call);
        self::assertEquals(1, $call->asap);
        self::assertStringContainsString('test', $call->attempt_note);

        Notification::assertSentTo($patient, PatientUnsuccessfulCallReplyNotification::class);
    }

    public function test_should_create_asap_call_to_nurse_from_phone_that_belongs_to_two_patients()
    {
        /** @var Collection $patients */
        $patients = collect([$this->createUsersOfType('participant', 1), $this->patient()]);
        PhoneNumber::whereIn('user_id', $patients->map(fn ($p) => $p->id))
            ->update(['number' => $patients->last()->getPhoneNumberForSms()]);

        $patient = $patients->last();

        $data     = $this->getSmsRequestData($patient->getPhoneNumberForSms(), 'test');
        $response = $this->post(route('twilio.sms.inbound'), $data);
        $response->assertStatus(200);

        /** @var Call $call */
        $call = Call::whereInboundCpmId($patient->id)
            ->where('type', '=', SchedulerService::TASK_TYPE)
            ->where('sub_type', '=', SchedulerService::SCHEDULE_NEXT_CALL_PER_PATIENT_SMS)
            ->first();
        self::assertNotNull($call);
        self::assertEquals(1, $call->asap);
        self::assertStringContainsString('test', $call->attempt_note);

        Notification::assertSentTo($patient, PatientUnsuccessfulCallReplyNotification::class);
    }

    public function test_should_create_one_asap_task_from_phone_that_belongs_to_two_patients()
    {
        /** @var Collection $patients */
        $aPatient = $this->createUsersOfType('participant', 1);
        $bPatient = $this->patient();
        PhoneNumber::whereIn('user_id', [$aPatient->id, $bPatient->id])
            ->update(['number' => $bPatient->getPhoneNumberForSms()]);

        $this->addDbEntryForNotification($aPatient);
        app(NurseFinderEloquentRepository::class)->assign($aPatient->id, $this->careCoach()->id);

        $data     = $this->getSmsRequestData($bPatient->getPhoneNumberForSms(), 'test');
        $response = $this->post(route('twilio.sms.inbound'), $data);
        $response->assertStatus(200);

        $calls = Call::whereIn('inbound_cpm_id', [$aPatient->id, $bPatient->id])
            ->where('type', '=', SchedulerService::TASK_TYPE)
            ->where('sub_type', '=', SchedulerService::SCHEDULE_NEXT_CALL_PER_PATIENT_SMS)
            ->get();
        self::assertEquals(1, $calls->count());
        self::assertTrue(1 === $calls->first()->asap);
        self::assertStringContainsString('test', $calls->first()->attempt_note);

        Notification::assertSentTo($aPatient, PatientUnsuccessfulCallReplyNotification::class);
        Notification::assertNotSentTo($bPatient, PatientUnsuccessfulCallReplyNotification::class);
    }

    public function test_should_not_create_more_than_one_asap_task_with_multiple_sms()
    {
        $patient  = $this->patient();
        $data     = $this->getSmsRequestData($patient->getPhoneNumberForSms(), 'test');
        $response = $this->post(route('twilio.sms.inbound'), $data);
        $response->assertStatus(200);

        $data     = $this->getSmsRequestData($patient->getPhoneNumberForSms(), 'test2');
        $response = $this->post(route('twilio.sms.inbound'), $data);
        $response->assertStatus(200);

        /** @var Call $call */
        $call = Call::whereInboundCpmId($patient->id)
            ->where('type', '=', SchedulerService::TASK_TYPE)
            ->where('sub_type', '=', SchedulerService::SCHEDULE_NEXT_CALL_PER_PATIENT_SMS)
            ->first();
        self::assertNotNull($call);
        self::assertEquals(1, $call->asap);
        self::assertStringContainsString("test\ntest2", $call->attempt_note);

        Notification::assertSentTo($patient, PatientUnsuccessfulCallReplyNotification::class);
    }

    private function addDbEntryForNotification(User $patient)
    {
        DatabaseNotification::create([
            'id'              => Str::random(36),
            'type'            => PatientUnsuccessfulCallNotification::class,
            'notifiable_id'   => $patient->id,
            'notifiable_type' => get_class($patient),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    private function getMailRequestData(string $fromEmail, string $body)
    {
        return [
            'FromName'          => 'Pangratios Cosma',
            'MessageStream'     => 'inbound',
            'From'              => $fromEmail,
            'FromFull'          => [],
            'To'                => 'ce336c4be369b05746140c3478913fbd@inbound.postmarkapp.com',
            'ToFull'            => [],
            'Cc'                => null,
            'CcFull'            => [],
            'Bcc'               => null,
            'BccFull'           => [],
            'OriginalRecipient' => 'ce336c4be369b05746140c3478913fbd@inbound.postmarkapp.com',
            'Subject'           => 'test',
            'MessageID'         => 'd456403a-dd57-4a77-89fd-8ce716db1647',
            'ReplyTo'           => null,
            'MailboxHash'       => null,
            'Date'              => 'Thu, 16 Jul 2020 08:56:26 +0000',
            'TextBody'          => $body,
            'HtmlBody'          => $body,
            'StrippedTextReply' => null,
            'Tag'               => null,
            'Headers'           => [],
            'Attachments'       => [],
        ];
    }

    private function getSmsRequestData(string $fromPhoneNumber, string $body)
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
