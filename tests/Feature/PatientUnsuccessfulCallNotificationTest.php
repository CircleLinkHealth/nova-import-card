<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use CircleLinkHealth\SharedModels\Entities\Call;
use App\Console\Commands\SendUnsuccessfulCallPatientsReminderNotification;
use CircleLinkHealth\CpmAdmin\Notifications\PatientUnsuccessfulCallNotification;
use App\NotificationsExclusion;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Core\Entities\DatabaseNotification;
use CircleLinkHealth\Core\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\Concerns\TwilioFake\Twilio;
use Tests\CustomerTestCase;

class PatientUnsuccessfulCallNotificationTest extends CustomerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        AppConfig::set('enable_unsuccessful_call_patient_notification', true);
        Mail::fake();
        Twilio::fake();
    }

    public function test_patient_does_not_receive_notification_after_second_unsuccessful_call()
    {
        Notification::fake();
        $nurse   = $this->careCoach();
        $patient = $this->patient();
        $this->be($nurse);
        $this->createNote($patient->id);
        Notification::assertSentTo($patient, PatientUnsuccessfulCallNotification::class);
        $this->createNote($patient->id);
        Notification::assertSentToTimes($patient, PatientUnsuccessfulCallNotification::class, 1);
    }

    public function test_patient_does_not_receive_notification_if_in_exclusion_list()
    {
        $patient = $this->patient();
        NotificationsExclusion::updateOrCreate([
            'user_id' => $patient->id,
            'sms'     => true,
            'mail'    => true,
        ], []);
        $nurse = $this->careCoach();
        $this->be($nurse);
        $this->createNote($patient->id);

        /** @var DatabaseNotification $notification */
        $notification = DatabaseNotification::whereType(PatientUnsuccessfulCallNotification::class)
            ->where('notifiable_id', '=', $patient->id)
            ->first();

        self::assertTrue('failed' === $notification->data['status']['mail']['value']);
        self::assertTrue('failed' === $notification->data['status']['twilio']['value']);
    }

    public function test_patient_does_not_receive_sms_notification_if_in_exclusion_list_but_receives_email()
    {
        $patient = $this->patient();
        NotificationsExclusion::updateOrCreate([
            'user_id' => $patient->id,
            'sms'     => true,
            'mail'    => false,
        ], []);
        $nurse = $this->careCoach();
        $this->be($nurse);
        $this->createNote($patient->id);

        /** @var DatabaseNotification $notification */
        $notification = DatabaseNotification::whereType(PatientUnsuccessfulCallNotification::class)
            ->where('notifiable_id', '=', $patient->id)
            ->first();

        self::assertFalse('failed' === $notification->data['status']['mail']['value']);
        self::assertTrue('failed' === $notification->data['status']['twilio']['value']);
    }

    public function test_patient_receives_notification_after_first_unsuccessful_call()
    {
        Notification::fake();
        $nurse   = $this->careCoach();
        $patient = $this->patient();
        $this->be($nurse);
        $this->createNote($patient->id);
        Notification::assertSentTo($patient, PatientUnsuccessfulCallNotification::class);
    }

    public function test_patient_receives_notification_after_third_unsuccessful_call()
    {
        Notification::fake();
        $nurse   = $this->careCoach();
        $patient = $this->patient();
        $this->be($nurse);
        $this->createNote($patient->id);
        Notification::assertSentTo($patient, PatientUnsuccessfulCallNotification::class);
        $this->createNote($patient->id);
        Notification::assertSentToTimes($patient, PatientUnsuccessfulCallNotification::class, 1);
        $this->createNote($patient->id);
        Notification::assertSentToTimes($patient, PatientUnsuccessfulCallNotification::class, 2);
    }

    public function test_patient_receives_only_one_reminder_after_two_days()
    {
        Notification::fake();
        $nurse   = $this->careCoach();
        $patient = $this->patient();
        $this->be($nurse);

        $this->createNote($patient->id);

        //need to have an entry in db for the SendUnsuccessfulCallPatientsReminderNotification command to work
        DatabaseNotification::create([
            'id'              => Str::random(36),
            'type'            => PatientUnsuccessfulCallNotification::class,
            'notifiable_id'   => $patient->id,
            'notifiable_type' => get_class($patient),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
        Notification::assertSentTo($patient, PatientUnsuccessfulCallNotification::class);

        Carbon::setTestNow(now()->addDays(2));

        //send notification reminder using command
        $this->artisan(SendUnsuccessfulCallPatientsReminderNotification::class);
        //add another entry that was created because of the command
        DatabaseNotification::create([
            'id'              => Str::random(36),
            'type'            => PatientUnsuccessfulCallNotification::class,
            'notifiable_id'   => $patient->id,
            'notifiable_type' => get_class($patient),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
        Notification::assertSentToTimes($patient, PatientUnsuccessfulCallNotification::class, 2);

        //reminder already sent, should not send another one
        $this->artisan(SendUnsuccessfulCallPatientsReminderNotification::class);
        Notification::assertSentToTimes($patient, PatientUnsuccessfulCallNotification::class, 2);
    }

    public function test_patient_receives_reminder_after_two_days()
    {
        Notification::fake();
        $nurse   = $this->careCoach();
        $patient = $this->patient();
        $this->be($nurse);
        $this->createNote($patient->id);
        Notification::assertSentTo($patient, PatientUnsuccessfulCallNotification::class);

        //need to have an entry in db for the command to work
        DatabaseNotification::create([
            'type'          => PatientUnsuccessfulCallNotification::class,
            'notifiable_id' => $patient->id,
        ]);

        Carbon::setTestNow(now()->addDays(2));
        $this->artisan(SendUnsuccessfulCallPatientsReminderNotification::class);
        Notification::assertSentToTimes($patient, PatientUnsuccessfulCallNotification::class, 2);
    }

    private function createNote($patientId)
    {
        $resp = $this->call(
            'POST',
            route('patient.note.store', ['patientId' => $patientId]),
            [
                'type'        => 'CCM Welcome Call',
                'phone'       => 1,
                'call_status' => Call::NOT_REACHED,
                'body'        => 'test',
                'patient_id'  => $patientId,
            ]
        );
    }
}
