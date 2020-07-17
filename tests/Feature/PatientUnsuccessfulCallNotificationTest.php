<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Call;
use App\Console\Commands\SendUnsuccessfulCallPatientsReminderNotification;
use App\Http\Controllers\NotesController;
use App\Notifications\PatientUnsuccessfulCallNotification;
use App\Services\Calls\SchedulerService;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\DatabaseNotification;
use CircleLinkHealth\Core\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\Concerns\TwilioFake\Twilio;
use Tests\CustomerTestCase;
use Tests\Helpers\MakesSafeRequests;

class PatientUnsuccessfulCallNotificationTest extends CustomerTestCase
{
    use MakesSafeRequests;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Mail::fake();
        Twilio::fake();
    }

    public function test_patient_does_not_receive_notification_after_second_unsuccessful_call()
    {
        $nurse   = $this->careCoach();
        $patient = $this->patient();
        $this->be($nurse);
        $this->createNote($patient->id);
        Notification::assertSentTo($patient, PatientUnsuccessfulCallNotification::class);
        $this->createNote($patient->id);
        Notification::assertSentToTimes($patient, PatientUnsuccessfulCallNotification::class, 1);
    }

    public function test_patient_receives_notification_after_first_unsuccessful_call()
    {
        $nurse   = $this->careCoach();
        $patient = $this->patient();
        $this->be($nurse);
        $this->createNote($patient->id);
        Notification::assertSentTo($patient, PatientUnsuccessfulCallNotification::class);
    }

    public function test_patient_receives_notification_after_third_unsuccessful_call()
    {
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
        $nurse   = $this->careCoach();
        $patient = $this->patient();
        $this->be($nurse);

        $this->createNote($patient->id);

        //need to have an entry in db for the SendUnsuccessfulCallPatientsReminderNotification command to work
        DatabaseNotification::create([
            'id'            => Str::random(36),
            'type'          => PatientUnsuccessfulCallNotification::class,
            'notifiable_id' => $patient->id,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
        Notification::assertSentTo($patient, PatientUnsuccessfulCallNotification::class);

        Carbon::setTestNow(now()->addDays(2));

        //send notification reminder using command
        $this->artisan(SendUnsuccessfulCallPatientsReminderNotification::class);
        //add another entry that was created because of the command
        DatabaseNotification::create([
            'id'            => Str::random(36),
            'type'          => PatientUnsuccessfulCallNotification::class,
            'notifiable_id' => $patient->id,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
        Notification::assertSentToTimes($patient, PatientUnsuccessfulCallNotification::class, 2);

        //reminder already sent, should not send another one
        $this->artisan(SendUnsuccessfulCallPatientsReminderNotification::class);
        Notification::assertSentToTimes($patient, PatientUnsuccessfulCallNotification::class, 2);
    }

    public function test_patient_receives_reminder_after_two_days()
    {
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
        /** @var NotesController $controller */
        $controller = app(NotesController::class);

        $req = $this->safeRequest(
            route('patient.note.store', ['patientId' => $patientId]),
            'POST',
            [
                'type'        => 'CCM Welcome Call',
                'phone'       => 1,
                'call_status' => Call::NOT_REACHED,
                'body'        => 'test',
                'patient_id'  => $patientId,
            ]
        );

        TestResponse::fromBaseResponse($controller->store($req, app(SchedulerService::class), $patientId));
    }
}
