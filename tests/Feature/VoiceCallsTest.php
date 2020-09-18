<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use CircleLinkHealth\SharedModels\Entities\Call;
use App\Console\Commands\CheckVoiceCalls;
use CircleLinkHealth\SharedModels\Entities\CpmCallAlert;
use App\Nova\Filters\TwilioCallSourceFilter;
use CircleLinkHealth\SharedModels\Services\SchedulerService;
use App\Traits\Tests\PracticeHelpers;
use App\Traits\Tests\TimeHelpers;
use App\TwilioCall;
use App\VoiceCall;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Entities\User;
use Tests\CustomerTestCase;

class VoiceCallsTest extends CustomerTestCase
{
    use PracticeHelpers;
    use TimeHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        AppConfig::set('enable_unsuccessful_call_patient_notification', false);
    }

    /**
     * Multiple Unsuccessful calls => No Voice Call Alert.
     */
    public function test_voice_call_alert_is_not_raised_with_multiple_unsuccessful_calls()
    {
        $practice = $this->practice();
        $this->setupExistingPractice($practice, true);

        /** @var User $patient */
        $patient = $this->patient();

        /** @var User $nurse */
        $nurse = $this->careCoach();

        /** @var SchedulerService $schedulerService */
        $schedulerService = app(SchedulerService::class);
        $call             = $schedulerService->storeScheduledCall($patient->id, '09:00', '17:00', now(), 'test', $nurse->id);

        $twilioCall = TwilioCall::create([
            'call_sid'                 => 'test',
            'call_status'              => 'completed',
            'source'                   => TwilioCallSourceFilter::PATIENT_CALL,
            'inbound_user_id'          => $patient->id,
            'outbound_user_id'         => $nurse->id,
            'call_duration'            => 35,
            'dial_conference_duration' => 0,
        ]);

        $twilioCall2 = TwilioCall::create([
            'call_sid'                 => 'test',
            'call_status'              => 'completed',
            'source'                   => TwilioCallSourceFilter::PATIENT_CALL,
            'inbound_user_id'          => $patient->id,
            'outbound_user_id'         => $nurse->id,
            'call_duration'            => 35,
            'dial_conference_duration' => 0,
        ]);

        // this will create a note, and update the $call->status to reached, which will eventually trigger the logic to match TwilioCall and Call (CallObserver.php)
        $this->addTime($nurse, $patient, 5, true, false, false, null, 0, 'Patient Note Creation', false, true);

        $call = $call->fresh();
        self::assertTrue(Call::NOT_REACHED === $call->status);

        $voiceCalls = $call->voiceCalls()->get();
        self::assertTrue($voiceCalls->isNotEmpty());
        self::assertEquals(2, $voiceCalls->count());

        /** @var VoiceCall $voiceCall */
        $voiceCall = $voiceCalls->first();
        self::assertEquals($call->id, $voiceCall->call_id);
        self::assertEquals(TwilioCall::class, $voiceCall->voice_callable_type);

        $twilioCall = $twilioCall->fresh();
        self::assertNotNull($twilioCall->voiceCall);
        self::assertEquals($voiceCall->id, $twilioCall->voiceCall->id);

        /** @var VoiceCall $voiceCall */
        $voiceCall2 = $voiceCalls->last();
        self::assertEquals($call->id, $voiceCall2->call_id);
        self::assertEquals(TwilioCall::class, $voiceCall2->voice_callable_type);

        $twilioCall2 = $twilioCall2->fresh();
        self::assertNotNull($twilioCall2->voiceCall);
        self::assertEquals($voiceCall2->id, $twilioCall2->voiceCall->id);

        $this->artisan(CheckVoiceCalls::class, ['from' => now()->subDay()]);

        $voiceCallCounts = CpmCallAlert::where('call_id', '=', $call->id)
            ->count();
        self::assertEquals(0, $voiceCallCounts);
    }

    /**
     * Multiple Unsuccessful & One long successful call => No Call Alert.
     */
    public function test_voice_call_alert_is_not_raised_with_multiple_unsuccessful_calls_one_successful_long()
    {
        $practice = $this->practice();
        $this->setupExistingPractice($practice, true);

        /** @var User $patient */
        $patient = $this->patient();

        /** @var User $nurse */
        $nurse = $this->careCoach();

        /** @var SchedulerService $schedulerService */
        $schedulerService = app(SchedulerService::class);
        $call             = $schedulerService->storeScheduledCall($patient->id, '09:00', '17:00', now(), 'test', $nurse->id);

        TwilioCall::create([
            'call_sid'                 => 'test',
            'call_status'              => 'completed',
            'source'                   => TwilioCallSourceFilter::PATIENT_CALL,
            'inbound_user_id'          => $patient->id,
            'outbound_user_id'         => $nurse->id,
            'call_duration'            => 35,
            'dial_conference_duration' => 0,
        ]);

        TwilioCall::create([
            'call_sid'                 => 'test',
            'call_status'              => 'completed',
            'source'                   => TwilioCallSourceFilter::PATIENT_CALL,
            'inbound_user_id'          => $patient->id,
            'outbound_user_id'         => $nurse->id,
            'call_duration'            => 35,
            'dial_conference_duration' => 0,
        ]);

        TwilioCall::create([
            'call_sid'                 => 'test',
            'call_status'              => 'completed',
            'source'                   => TwilioCallSourceFilter::PATIENT_CALL,
            'inbound_user_id'          => $patient->id,
            'outbound_user_id'         => $nurse->id,
            'call_duration'            => 4 * 60,
            'dial_conference_duration' => 4 * 55,
        ]);

        // this will create a note, and update the $call->status to reached, which will eventually trigger the logic to match TwilioCall and Call (CallObserver.php)
        $this->addTime($nurse, $patient, 5, true, true, false, null, 0, 'Patient Note Creation', false, true);

        $call = $call->fresh();
        self::assertTrue(Call::REACHED === $call->status);

        $voiceCalls = $call->voiceCalls()->get();
        self::assertTrue($voiceCalls->isNotEmpty());
        self::assertEquals(3, $voiceCalls->count());

        $this->artisan(CheckVoiceCalls::class, ['from' => now()->subDay()]);

        $voiceCallCounts = CpmCallAlert::where('call_id', '=', $call->id)
            ->count();
        self::assertEquals(0, $voiceCallCounts);
    }

    /**
     * Unsuccessful call => No Voice Call Alert.
     */
    public function test_voice_call_alert_is_not_raised_with_unsuccessful_call()
    {
        $practice = $this->practice();
        $this->setupExistingPractice($practice, true);

        /** @var User $patient */
        $patient = $this->patient();

        /** @var User $nurse */
        $nurse = $this->careCoach();

        /** @var SchedulerService $schedulerService */
        $schedulerService = app(SchedulerService::class);
        $call             = $schedulerService->storeScheduledCall($patient->id, '09:00', '17:00', now(), 'test', $nurse->id);

        TwilioCall::create([
            'call_sid'                 => 'test',
            'call_status'              => 'completed',
            'source'                   => TwilioCallSourceFilter::PATIENT_CALL,
            'inbound_user_id'          => $patient->id,
            'outbound_user_id'         => $nurse->id,
            'call_duration'            => 35,
            'dial_conference_duration' => 0,
        ]);

        // this will create a note, and update the $call->status to reached, which will eventually trigger the logic to match TwilioCall and Call (CallObserver.php)
        $this->addTime($nurse, $patient, 5, true, false, false, null, 0, 'Patient Note Creation', false, true);

        $this->artisan(CheckVoiceCalls::class, ['from' => now()->subDay()]);

        $voiceCallCounts = CpmCallAlert::where('call_id', '=', $call->id)
            ->count();
        self::assertEquals(0, $voiceCallCounts);
    }

    /**
     * Successful call but duration is smaller that threshold => Voice Call Alert.
     */
    public function test_voice_call_alert_is_raised_one_successful_short()
    {
        $practice = $this->practice();
        $this->setupExistingPractice($practice, true);

        /** @var User $patient */
        $patient = $this->patient();

        /** @var User $nurse */
        $nurse = $this->careCoach();

        /** @var SchedulerService $schedulerService */
        $schedulerService = app(SchedulerService::class);
        $call             = $schedulerService->storeScheduledCall($patient->id, '09:00', '17:00', now(), 'test', $nurse->id);

        $twilioCall = TwilioCall::create([
            'call_sid'                 => 'test',
            'call_status'              => 'completed',
            'source'                   => TwilioCallSourceFilter::PATIENT_CALL,
            'inbound_user_id'          => $patient->id,
            'outbound_user_id'         => $nurse->id,
            'call_duration'            => 35,
            'dial_conference_duration' => 60,
        ]);

        // this will create a note, and update the $call->status to reached, which will eventually trigger the logic to match TwilioCall and Call (CallObserver.php)
        $this->addTime($nurse, $patient, 5, true, true, false, null, 0, 'Patient Note Creation', false, true);

        $this->artisan(CheckVoiceCalls::class, ['from' => now()->subDay()]);

        self::assertTrue(CpmCallAlert::where('call_id', '=', $call->id)->exists());
    }

    /**
     * Unsuccessful call but duration is larger that threshold => Voice Call Alert.
     */
    public function test_voice_call_alert_is_raised_one_unsuccessful_long()
    {
        $practice = $this->practice();
        $this->setupExistingPractice($practice, true);

        /** @var User $patient */
        $patient = $this->patient();

        /** @var User $nurse */
        $nurse = $this->careCoach();

        /** @var SchedulerService $schedulerService */
        $schedulerService = app(SchedulerService::class);
        $call             = $schedulerService->storeScheduledCall($patient->id, '09:00', '17:00', now(), 'test', $nurse->id);

        $twilioCall = TwilioCall::create([
            'call_sid'                 => 'test',
            'call_status'              => 'completed',
            'source'                   => TwilioCallSourceFilter::PATIENT_CALL,
            'inbound_user_id'          => $patient->id,
            'outbound_user_id'         => $nurse->id,
            'call_duration'            => 4 * 60,
            'dial_conference_duration' => 4 * 55,
        ]);

        // this will create a note, and update the $call->status to reached, which will eventually trigger the logic to match TwilioCall and Call (CallObserver.php)
        $this->addTime($nurse, $patient, 5, true, false, false, null, 0, 'Patient Note Creation', false, true);

        $this->artisan(CheckVoiceCalls::class, ['from' => now()->subDay()]);

        self::assertTrue(CpmCallAlert::where('call_id', '=', $call->id)->exists());
    }

    /**
     * Multiple Unsuccessful & One short successful call => Call Alert.
     */
    public function test_voice_call_alert_is_raised_with_multiple_unsuccessful_calls_one_successful_short()
    {
        $practice = $this->practice();
        $this->setupExistingPractice($practice, true);

        /** @var User $patient */
        $patient = $this->patient();

        /** @var User $nurse */
        $nurse = $this->careCoach();

        /** @var SchedulerService $schedulerService */
        $schedulerService = app(SchedulerService::class);
        $call             = $schedulerService->storeScheduledCall($patient->id, '09:00', '17:00', now(), 'test', $nurse->id);

        TwilioCall::create([
            'call_sid'                 => 'test',
            'call_status'              => 'completed',
            'source'                   => TwilioCallSourceFilter::PATIENT_CALL,
            'inbound_user_id'          => $patient->id,
            'outbound_user_id'         => $nurse->id,
            'call_duration'            => 35,
            'dial_conference_duration' => 0,
        ]);

        TwilioCall::create([
            'call_sid'                 => 'test',
            'call_status'              => 'completed',
            'source'                   => TwilioCallSourceFilter::PATIENT_CALL,
            'inbound_user_id'          => $patient->id,
            'outbound_user_id'         => $nurse->id,
            'call_duration'            => 35,
            'dial_conference_duration' => 0,
        ]);

        TwilioCall::create([
            'call_sid'                 => 'test',
            'call_status'              => 'completed',
            'source'                   => TwilioCallSourceFilter::PATIENT_CALL,
            'inbound_user_id'          => $patient->id,
            'outbound_user_id'         => $nurse->id,
            'call_duration'            => 1 * 60,
            'dial_conference_duration' => 1 * 55,
        ]);

        // this will create a note, and update the $call->status to reached, which will eventually trigger the logic to match TwilioCall and Call (CallObserver.php)
        $this->addTime($nurse, $patient, 5, true, true, false, null, 0, 'Patient Note Creation', false, true);

        $call = $call->fresh();
        self::assertTrue(Call::REACHED === $call->status);

        $voiceCalls = $call->voiceCalls()->get();
        self::assertTrue($voiceCalls->isNotEmpty());
        self::assertEquals(3, $voiceCalls->count());

        $this->artisan(CheckVoiceCalls::class, ['from' => now()->subDay()]);

        self::assertTrue(CpmCallAlert::where('call_id', '=', $call->id)->exists());
    }

    /**
     * Nurse calls patient, hangs up and then saves the note.
     *
     * 1. Schedule Call (calls table)
     * 2. Create Twilio Call (twilio_calls table)
     * 3. Create Note with successful call
     * 4. Assert that Call and Twilio Call are associated in voice_calls.
     *
     * @return void
     */
    public function test_voice_call_is_correctly_assigned_to_cpm_call_after_save_note()
    {
        $practice = $this->practice();
        $this->setupExistingPractice($practice, true);

        /** @var User $patient */
        $patient = $this->patient();

        /** @var User $nurse */
        $nurse = $this->careCoach();

        /** @var SchedulerService $schedulerService */
        $schedulerService = app(SchedulerService::class);
        $call             = $schedulerService->storeScheduledCall($patient->id, '09:00', '17:00', now(), 'test', $nurse->id);

        $twilioCall = TwilioCall::create([
            'call_sid'                 => 'test',
            'call_status'              => 'completed',
            'source'                   => TwilioCallSourceFilter::PATIENT_CALL,
            'inbound_user_id'          => $patient->id,
            'outbound_user_id'         => $nurse->id,
            'call_duration'            => 4 * 60,
            'dial_conference_duration' => 4 * 60,
        ]);

        // this will create a note, and update the $call->status to reached, which will eventually trigger the logic to match TwilioCall and Call (CallObserver.php)
        $this->addTime($nurse, $patient, 5, true, true, false, null, 0, 'Patient Note Creation', false, true);

        $call = $call->fresh();
        self::assertTrue(Call::REACHED === $call->status);

        $voiceCalls = $call->voiceCalls()->get();
        self::assertTrue($voiceCalls->isNotEmpty());

        /** @var VoiceCall $voiceCall */
        $voiceCall = $voiceCalls->first();
        self::assertEquals($call->id, $voiceCall->call_id);
        self::assertEquals(TwilioCall::class, $voiceCall->voice_callable_type);

        $twilioCall = $twilioCall->fresh();
        self::assertNotNull($twilioCall->voiceCall);
        self::assertEquals($voiceCall->id, $twilioCall->voiceCall->id);
    }

    /**
     * Nurse calls patient, hangs up and then saves the note.
     *
     * 1. Create Twilio Call (twilio_calls table)
     * 2. Create Note with successful call
     * 3. Assert that Call (should be created with Note) and Twilio Call are associated in voice_calls.
     *
     * @return void
     */
    public function test_voice_call_is_correctly_assigned_to_cpm_call_after_save_note_without_scheduled_call()
    {
        $practice = $this->practice();
        $this->setupExistingPractice($practice, true);

        /** @var User $patient */
        $patient = $this->patient();

        /** @var User $nurse */
        $nurse = $this->careCoach();

        $twilioCall = TwilioCall::create([
            'call_sid'                 => 'test',
            'call_status'              => 'completed',
            'source'                   => TwilioCallSourceFilter::PATIENT_CALL,
            'inbound_user_id'          => $patient->id,
            'outbound_user_id'         => $nurse->id,
            'call_duration'            => 4 * 60,
            'dial_conference_duration' => 4 * 60,
        ]);

        // this will create a note, and update the $call->status to reached, which will eventually trigger the logic to match TwilioCall and Call (CallObserver.php)
        $note = $this->addTime($nurse, $patient, 5, true, true, false, null, 0, 'Patient Note Creation', false, true);

        $call = $note->call;
        self::assertTrue(Call::REACHED === $call->status);

        $voiceCalls = $call->voiceCalls()->get();
        self::assertTrue($voiceCalls->isNotEmpty());

        /** @var VoiceCall $voiceCall */
        $voiceCall = $voiceCalls->first();
        self::assertEquals($call->id, $voiceCall->call_id);
        self::assertEquals(TwilioCall::class, $voiceCall->voice_callable_type);

        $twilioCall = $twilioCall->fresh();
        self::assertNotNull($twilioCall->voiceCall);
        self::assertEquals($voiceCall->id, $twilioCall->voiceCall->id);
    }

    /**
     * Nurse calls patient, saves note and then hangs up.
     *
     * 1. Schedule Call (calls table)
     * 2. Create Note with successful call
     * 3. Create Twilio Call (twilio_calls table)
     * 4. Assert that Call and Twilio Call are associated in voice_calls.
     *
     * @return void
     */
    public function test_voice_call_is_correctly_assigned_to_cpm_call_after_voice_call()
    {
        $practice = $this->practice();
        $this->setupExistingPractice($practice, true);

        /** @var User $patient */
        $patient = $this->patient();

        /** @var User $nurse */
        $nurse = $this->careCoach();

        /** @var SchedulerService $schedulerService */
        $schedulerService = app(SchedulerService::class);
        $call             = $schedulerService->storeScheduledCall($patient->id, '09:00', '17:00', now(), 'test', $nurse->id);

        // this will create a note, and update the $call->status to reached, which will eventually trigger the logic to match TwilioCall and Call (CallObserver.php)
        $this->addTime($nurse, $patient, 5, true, true, false, null, 0, 'Patient Note Creation', false, true);

        $twilioCall = TwilioCall::create([
            'call_sid'                 => 'test',
            'call_status'              => 'completed',
            'source'                   => TwilioCallSourceFilter::PATIENT_CALL,
            'inbound_user_id'          => $patient->id,
            'outbound_user_id'         => $nurse->id,
            'call_duration'            => 4 * 60,
            'dial_conference_duration' => 4 * 60,
        ]);

        $call = $call->fresh();
        self::assertTrue(Call::REACHED === $call->status);

        $voiceCalls = $call->voiceCalls()->get();
        self::assertTrue($voiceCalls->isNotEmpty());

        /** @var VoiceCall $voiceCall */
        $voiceCall = $voiceCalls->first();
        self::assertEquals($call->id, $voiceCall->call_id);
        self::assertEquals(TwilioCall::class, $voiceCall->voice_callable_type);

        $twilioCall = $twilioCall->fresh();
        self::assertNotNull($twilioCall->voiceCall);
        self::assertEquals($voiceCall->id, $twilioCall->voiceCall->id);
    }

    /**
     * Nurse calls patient, saves note and then hangs up.
     *
     * 1.  Schedule Call (calls table)
     * 2.1 Create Twilio Call (twilio_calls table) - dropped call / no answer
     * 2.2 Create another Twilio Call
     * 3.  Create Note with successful call
     * 4.  Assert that Call and Twilio Call are associated in voice_calls.
     *
     * @return void
     */
    public function test_voice_calls_are_correctly_assigned_to_cpm_call_after_save_note()
    {
        $practice = $this->practice();
        $this->setupExistingPractice($practice, true);

        /** @var User $patient */
        $patient = $this->patient();

        /** @var User $nurse */
        $nurse = $this->careCoach();

        /** @var SchedulerService $schedulerService */
        $schedulerService = app(SchedulerService::class);
        $call             = $schedulerService->storeScheduledCall($patient->id, '09:00', '17:00', now(), 'test', $nurse->id);

        $twilioCall = TwilioCall::create([
            'call_sid'                 => 'test',
            'call_status'              => 'completed',
            'source'                   => TwilioCallSourceFilter::PATIENT_CALL,
            'inbound_user_id'          => $patient->id,
            'outbound_user_id'         => $nurse->id,
            'call_duration'            => 35,
            'dial_conference_duration' => 0,
        ]);

        $twilioCall2 = TwilioCall::create([
            'call_sid'                 => 'test',
            'call_status'              => 'completed',
            'source'                   => TwilioCallSourceFilter::PATIENT_CALL,
            'inbound_user_id'          => $patient->id,
            'outbound_user_id'         => $nurse->id,
            'call_duration'            => 4 * 60,
            'dial_conference_duration' => 4 * 60,
        ]);

        // this will create a note, and update the $call->status to reached, which will eventually trigger the logic to match TwilioCall and Call (CallObserver.php)
        $this->addTime($nurse, $patient, 5, true, true, false, null, 0, 'Patient Note Creation', false, true);

        $call = $call->fresh();
        self::assertTrue(Call::REACHED === $call->status);

        $voiceCalls = $call->voiceCalls()->get();
        self::assertTrue($voiceCalls->isNotEmpty());
        self::assertEquals(2, $voiceCalls->count());

        /** @var VoiceCall $voiceCall */
        $voiceCall = $voiceCalls->first();
        self::assertEquals($call->id, $voiceCall->call_id);
        self::assertEquals(TwilioCall::class, $voiceCall->voice_callable_type);

        $twilioCall = $twilioCall->fresh();
        self::assertNotNull($twilioCall->voiceCall);
        self::assertEquals($voiceCall->id, $twilioCall->voiceCall->id);

        /** @var VoiceCall $voiceCall */
        $voiceCall2 = $voiceCalls->last();
        self::assertEquals($call->id, $voiceCall2->call_id);
        self::assertEquals(TwilioCall::class, $voiceCall2->voice_callable_type);

        $twilioCall2 = $twilioCall2->fresh();
        self::assertNotNull($twilioCall2->voiceCall);
        self::assertEquals($voiceCall2->id, $twilioCall2->voiceCall->id);
    }

    /**
     * Nurse calls patient, saves note and then hangs up.
     *
     * 1. Schedule Call (calls table)
     * 2. Create Twilio Call (twilio_calls table) - dropped call / no answer
     * 3. Create Note with successful call
     * 4. Create another Twilio Call (the successful call)
     * 5. Assert that Call and Twilio Call are associated in voice_calls.
     *
     * @return void
     */
    public function test_voice_calls_are_correctly_assigned_to_cpm_call_after_voice_calls()
    {
        $practice = $this->practice();
        $this->setupExistingPractice($practice, true);

        /** @var User $patient */
        $patient = $this->patient();

        /** @var User $nurse */
        $nurse = $this->careCoach();

        /** @var SchedulerService $schedulerService */
        $schedulerService = app(SchedulerService::class);
        $call             = $schedulerService->storeScheduledCall($patient->id, '09:00', '17:00', now(), 'test', $nurse->id);

        $twilioCall = TwilioCall::create([
            'call_sid'                 => 'test',
            'call_status'              => 'completed',
            'source'                   => TwilioCallSourceFilter::PATIENT_CALL,
            'inbound_user_id'          => $patient->id,
            'outbound_user_id'         => $nurse->id,
            'call_duration'            => 35,
            'dial_conference_duration' => 0,
        ]);

        // this will create a note, and update the $call->status to reached, which will eventually trigger the logic to match TwilioCall and Call (CallObserver.php)
        $this->addTime($nurse, $patient, 5, true, true, false, null, 0, 'Patient Note Creation', false, true);

        $twilioCall2 = TwilioCall::create([
            'call_sid'                 => 'test',
            'call_status'              => 'completed',
            'source'                   => TwilioCallSourceFilter::PATIENT_CALL,
            'inbound_user_id'          => $patient->id,
            'outbound_user_id'         => $nurse->id,
            'call_duration'            => 4 * 60,
            'dial_conference_duration' => 4 * 60,
        ]);

        $call = $call->fresh();
        self::assertTrue(Call::REACHED === $call->status);

        $voiceCalls = $call->voiceCalls()->get();
        self::assertTrue($voiceCalls->isNotEmpty());
        self::assertEquals(2, $voiceCalls->count());

        /** @var VoiceCall $voiceCall */
        $voiceCall = $voiceCalls->first();
        self::assertEquals($call->id, $voiceCall->call_id);
        self::assertEquals(TwilioCall::class, $voiceCall->voice_callable_type);

        $twilioCall = $twilioCall->fresh();
        self::assertNotNull($twilioCall->voiceCall);
        self::assertEquals($voiceCall->id, $twilioCall->voiceCall->id);

        /** @var VoiceCall $voiceCall */
        $voiceCall2 = $voiceCalls->last();
        self::assertEquals($call->id, $voiceCall2->call_id);
        self::assertEquals(TwilioCall::class, $voiceCall2->voice_callable_type);

        $twilioCall2 = $twilioCall2->fresh();
        self::assertNotNull($twilioCall2->voiceCall);
        self::assertEquals($voiceCall2->id, $twilioCall2->voiceCall->id);
    }
}
