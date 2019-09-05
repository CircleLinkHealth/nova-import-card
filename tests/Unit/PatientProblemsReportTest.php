<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Console\Commands\CreatePatientProblemsReportForPractice;
use App\Exports\PatientProblemsReport;
use App\Notifications\SendSignedUrlToDownloadPatientProblemsReport;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Notification;
use Tests\Helpers\SetupTestCustomerTrait;
use Tests\TestCase;

class PatientProblemsReportTest extends TestCase
{
    use SetupTestCustomerTrait;

    /**
     * The console command is essentially a way to call PatientProblemsReport from the command line.
     * We wnat to test that it sends the notification.
     *
     * @see PatientProblemsReport
     */
    public function test_console_command_sends_notification()
    {
        //setup
        Notification::fake();
        $customer = $this->createTestCustomerData(1);

        //test
        $this->artisan(CreatePatientProblemsReportForPractice::class, [
            'practice_id' => $customer['practice']->id,
            'user_id'     => $customer['admin']->id,
        ])
            ->assertExitCode(0)
            ->expectsOutput('Command ran.');

        //assert
        $this->assertTestMediaIsAttachedToPracticeAndNotUser($customer['practice'], $customer['admin']);
        $this->assertNotificationSent($customer['admin']);
    }

    public function test_model_not_found_exception_thrown_if_user_passed_does_not_have_access_to_practice()
    {
        //setup
        $customer1 = $this->createTestCustomerData(0);
        $customer2 = $this->createTestCustomerData(0);
        $report    = new PatientProblemsReport();

        try {
            //test
            $report->forPractice($customer1['practice']->id)
                ->forUser($customer2['admin']->id);
        } catch (\Exception $e) {
            //assert
            $this->assertEquals(ModelNotFoundException::class, get_class($e));
        }
    }

    public function test_other_users_from_the_same_practice_do_not_have_access_to_the_report()
    {
        //setup
        $customer = $this->createTestCustomerData(1);
        $user     = $customer['provider'];
        $user2    = $customer['admin'];
        $report   = new PatientProblemsReport();

        //test
        $report->forPractice($customer['practice']->id)
            ->forUser($user->id)
            ->createMedia()
            ->notifyUser();

        $this->verifyUserIsRedirectedToLoginIfUnauthenticated($report->getSignedLink());

        $response = $this->actingAs($user2)->call('get', $report->getSignedLink());

        //assert
        $response->assertStatus(403);
        $this->assertTestMediaIsAttachedToPracticeAndNotUser($customer['practice'], $user);
        $this->assertNotificationSent($user);
    }

    /**
     * Assert that the SendSignedUrlToDownloadPatientProblemsReport was sent to the given user.
     *
     * @param User $user
     */
    private function assertNotificationSent(User $user)
    {
        Notification::assertSentTo(
            $user,
            SendSignedUrlToDownloadPatientProblemsReport::class,
            function ($notification, $channels, $notifiable) use ($user) {
                $this->verifyUserIsRedirectedToLoginIfUnauthenticated($notification->signedLink);

                $response = $this->actingAs($user)->call('get', $notification->signedLink);

                $response->assertStatus(200)
                    ->assertHeader('content-type', 'text/plain; charset=UTF-8');

                $this->assertEquals(['database', 'mail'], $channels);

                return (int) $notifiable->id === (int) $user->id;
            }
        );
    }

    private function assertTestMediaIsAttachedToPracticeAndNotUser(Practice $practice, User $user)
    {
        $this->assertDatabaseHas('media', [
            'model_type' => 'CircleLinkHealth\Customer\Entities\Practice',
            'model_id'   => $practice->id,
        ]);

        //A safeguard to help us remember we should never attach practice reports to users directly, as they contain too much sensitive data.
        $this->assertDatabaseMissing('media', [
            'model_type' => 'CircleLinkHealth\Customer\Entities\User',
            'model_id'   => $user->id,
        ]);
    }

    private function verifyUserIsRedirectedToLoginIfUnauthenticated($signedLink)
    {
        $response = $this->call('get', $signedLink)
            ->assertRedirect(url('/login'));
    }
}
