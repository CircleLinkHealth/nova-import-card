<?php

namespace Tests\Feature;

use App\NotifiableUser;
use App\Notifications\SurveyInvitationLink;
use App\Services\SurveyInvitationLinksService;
use App\Survey;
use App\User;
use Illuminate\Support\Facades\Notification;
use Tests\Helpers\PatientHelpers;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;

class HraSurveyReminderTest extends TestCase
{
    use UserHelpers;
    use PatientHelpers;

    protected function setUp()
    {
        parent::setUp();
        (new \SurveySeeder())->run();
    }

    /**
     * Test that reminder is sent to both phone and email 10 days prior
     *
     * @return void
     */
    public function test_reminder_sent_10_days_prior()
    {
        $this->be($this->createAdminUser());
        $this->sendReminderForAppointmentsIn(10);
    }

    /**
     * Test that reminder is sent to both phone and email 8 days prior
     *
     * @return void
     */
    public function test_reminder_sent_8_days_prior()
    {
        $this->be($this->createAdminUser());
        $this->sendReminderForAppointmentsIn(8);
    }

    private function sendReminderForAppointmentsIn(int $days)
    {
        Notification::fake();

        $service = app(SurveyInvitationLinksService::class);

        $date    = now()->addDays($days);
        $patient = $this->createPatient();
        $patient->addAppointment($date);

        $appointment = $patient->latestAwvAppointment()->appointment;
        $isIn10Days  = $appointment->isBetween($date->copy()->startOfDay(), $date->copy()->endOfDay());
        $this->assertTrue($isIn10Days);

        User::ofType('participant', false)
            ->whereHas('awvAppointments', function ($q) use ($date) {
                $q->whereBetween('appointment',
                    [$date->copy()->startOfDay(), $date->copy()->endOfDay()]);
            })
            ->with([
                'surveyInstances' => function ($query) {
                    $query->ofSurvey(Survey::HRA)->mostRecent();
                },
            ])
            ->get()
            ->each(function (User $user) use ($service) {
                try {
                    $url = $service->createAndSaveUrl($user, Survey::HRA, true);
                } catch (\Exception $e) {
                    throw $e;
                }

                $practiceName     = optional($user->primaryPractice)->display_name;
                $providerFullName = optional($user->billingProviderUser())->getFullName();
                $appointment      = $user->latestAwvAppointment()->appointment;
                $notifiableUser = new NotifiableUser($user);
                $notifiableUser->notify(new SurveyInvitationLink($url, Survey::HRA, null, $practiceName, $providerFullName,
                    $appointment));

                Notification::assertSentTo($notifiableUser, SurveyInvitationLink::class,
                    function ($notification, $channels) {
                        //Twilio and Email
                        return sizeof($channels) === 2;
                    });
            });
    }
}
