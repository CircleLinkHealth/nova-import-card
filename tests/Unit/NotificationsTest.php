<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Notifications\CarePlanApprovalReminder;
use App\Notifications\CarePlanProviderApproved;
use App\Notifications\Channels\DirectMailChannel;
use App\Notifications\Channels\FaxChannel;
use App\Notifications\NBISupplementaryDataNotFound;
use App\Notifications\NoteForwarded;
use App\Notifications\ResetPassword;
use App\Notifications\WeeklyPracticeReport;
use App\Notifications\WeeklyProviderReport;
use Carbon\Carbon;
use CircleLinkHealth\Core\Notifications\Channels\DatabaseChannel;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Facades\Notification;
use Tests\Helpers\CarePlanHelpers;
use Tests\Helpers\SetupTestCustomerTrait;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use CarePlanHelpers;
    use SetupTestCustomerTrait;

    protected $channels;

    protected $location;

    protected $nurse;

    protected $patient;

    protected $patients;

    protected $practice;

    protected $provider;

    protected function setUp()
    {
        parent::setUp();

        $this->channels = $channels = [
            DirectMailChannel::class,
            FaxChannel::class,
            DatabaseChannel::class,
        ];

        $data = $this->createTestCustomerData(1);

        $this->practice = $data['practice'];
        $this->location = $data['location'];
        $this->patients = $data['patients'];
        $this->provider = $data['provider'];

        $this->nurse   = $this->createUser($this->practice->id, 'care-center');
        $this->patient = $this->patients->first();

        $carePerson = $this->patient->careTeamMembers()->create([
            'member_user_id' => $this->nurse->id,
            'type'           => 'member',
            'alert'          => 1,
        ]);

        $this->patient->notes()->create([
            'author_id'    => $this->nurse->id,
            'body'         => 'test',
            'logger_id'    => $this->nurse->id,
            'performed_at' => Carbon::now(),
            'type'         => 'Patient Consented',
        ]);
    }

    public function test_it_sends_careplan_approval_reminder()
    {
        Notification::fake();

        $reminder = new CarePlanApprovalReminder(5);
        $this->provider->notify($reminder);

        Notification::assertSentTo(
            [$this->provider],
            CarePlanApprovalReminder::class
        );

        Notification::assertNotSentTo(
            [$this->patient],
            CarePlanApprovalReminder::class
        );

        Notification::assertSentTo(
            $this->provider,
            CarePlanApprovalReminder::class,
            function ($notification, $channels) use ($reminder) {
                $mailData = $notification->toMail($this->provider)->build();
                $this->assertEquals('5 CircleLink Care Plan(s) for your Approval!', $mailData->subject);
                $this->assertEquals('CircleLink Health', $mailData->from[0]['name']);
                $this->assertEquals('notifications@careplanmanager.com', $mailData->from[0]['address']);
                $this->assertEquals("{$this->provider->getFullName()}", $mailData->to[0]['name']);
                $this->assertEquals("{$this->provider->email}", $mailData->to[0]['address']);
                $this->assertEquals('emails.careplansPendingApproval', $mailData->view);

                return $notification->id === $reminder->id;
            }
        );
    }

    public function test_it_sends_careplan_provider_approved()
    {
        Notification::fake();

        $carePlanApproved = new CarePlanProviderApproved($this->patient->carePlan, $this->channels);
        $this->location->notify($carePlanApproved);

        Notification::assertSentTo(
            [$this->location],
            CarePlanProviderApproved::class
        );

        Notification::assertNotSentTo(
            [$this->provider],
            CarePlanProviderApproved::class
        );

        Notification::assertSentTo(
            $this->location,
            CarePlanProviderApproved::class,
            function ($notification, $channels) use ($carePlanApproved) {
                $mailData = $notification->toMail($this->location);

                $this->assertEquals('A CarePlan has just been approved', $mailData->subject);
                $this->assertEquals('CircleLink Health', $mailData->from[1]);
                $this->assertEquals('no-reply@circlelinkhealth.com', $mailData->from[0]);
                $this->assertEquals('raph@circlelinkhealth.com', $mailData->bcc[0][0]);
                $this->assertEquals('vendor.notifications.email', $mailData->view);
                $this->assertEquals("Please click below button to see a Care Plan regarding one of your patients, which was approved on {$this->patient->carePlan->provider_date->toFormattedDateString()} by ", $mailData->viewData['greeting']);
                $this->assertEquals('View CarePlan', $mailData->viewData['actionText']);

                return $notification->id === $carePlanApproved->id;
            }
        );
    }

    /**
     * A basic test example.
     */
    public function tests_other_notifications()
    {
        Notification::fake();

        $this->location->notify(new NoteForwarded($this->patient->notes()->first(), $this->channels));

        $token = 'asdfg';
        $this->provider->notify(new ResetPassword($token));
        Notification::assertSentTo(
            [$this->provider],
            ResetPassword::class
        );

        $this->provider->notify(new WeeklyPracticeReport([], 'test'));
        Notification::assertSentTo(
            [$this->provider],
            WeeklyPracticeReport::class
        );

        $this->provider->notify(new WeeklyProviderReport([]));
        Notification::assertSentTo(
            [$this->provider],
            WeeklyProviderReport::class
        );

        $newUser = (new User())->forceFill([
            'name'  => 'Test User',
            'email' => 'email@example.com',
        ]);
        $newUser->notify(new NBISupplementaryDataNotFound($this->patient));
        Notification::assertSentTo(
            [$newUser],
            NBISupplementaryDataNotFound::class
        );
    }
}
