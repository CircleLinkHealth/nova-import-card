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
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Notification;
use Tests\Helpers\SetupTestCustomerTrait;
use Tests\TestCase;
use URL;

class PatientProblemsReportTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    use SetupTestCustomerTrait;

    public function test__user_is_redirected_to_login_if_unauthenticated()
    {
        $signedLink = URL::temporarySignedRoute('download.media.from.signed.url', now()->addDays(2), [
            'media_id'    => 1,
            'user_id'     => 2,
            'practice_id' => 3,
        ]);

        $this->call('get', $signedLink)
            ->assertRedirect(url('/login'));
    }

    /**
     * We want to test that given the correct input, the command will produce the report.
     *
     * @see PatientProblemsReport
     */
    public function test_console_command_sends_notification()
    {
        //setup
        $userId     = 1;
        $practiceId = 10;
        $mock       = \Mockery::mock(PatientProblemsReport::class);

        $mock->shouldReceive('forPractice')
            ->with($practiceId)
            ->andReturnSelf()
            ->shouldReceive('forUser')
            ->with($userId)
            ->andReturnSelf()
            ->shouldReceive('createMedia')
            ->andReturnSelf()
            ->shouldReceive('notifyUser')
            ->andReturnSelf();

        $this->instance(PatientProblemsReport::class, $mock);

        //test
        $this->artisan(CreatePatientProblemsReportForPractice::class, [
            'practice_id' => $practiceId,
            'user_id'     => $userId,
        ])
            ->assertExitCode(0)
            ->expectsOutput('Command ran.');
    }

    /**
     * This tests a safeguard against trying to create a report for a user who does not have access to the practice.
     */
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

    public function test_notification_is_sent()
    {
        //setup
        Notification::fake();
        $customer = $this->createTestCustomerData(5);
        $user     = $customer['provider'];
        $practice = $customer['practice'];
        $report   = new PatientProblemsReport();

        //test
        $report->forPractice($practice->id)
            ->forUser($user->id)
            ->createMedia()
            ->notifyUser();

        //assert
        $this->assertDatabaseHas('media', [
            'model_type' => 'CircleLinkHealth\Customer\Entities\Practice',
            'model_id'   => $practice->id,
        ]);

        //A safeguard to help us remember we should never attach practice reports to users directly, as they contain too much sensitive data.
        $this->assertDatabaseMissing('media', [
            'model_type' => 'CircleLinkHealth\Customer\Entities\User',
            'model_id'   => $user->id,
        ]);

        Notification::assertSentTo(
            $user,
            SendSignedUrlToDownloadPatientProblemsReport::class,
            function ($notification, $channels, $notifiable) use ($user) {
                $response = $this->actingAs($user)->call('get', $notification->signedLink);

                $response->assertStatus(200)
                    ->assertHeader('content-type', 'text/plain; charset=UTF-8');

                $this->assertEquals(['database', 'mail'], $channels);

                return (int) $notifiable->id === (int) $user->id;
            }
        );
    }

    public function test_other_users_from_the_same_practice_do_not_have_access_to_the_report()
    {
        //setup
        Notification::fake();
        $customer = $this->createTestCustomerData(1);
        $user     = $customer['provider'];
        $user2    = $customer['admin'];
        $report   = new PatientProblemsReport();

        //test
        $report->forPractice($customer['practice']->id)
            ->forUser($user->id)
            ->createMedia()
            ->notifyUser();

        $this->actingAs($user2)->call('get', $report->getSignedLink())
            //assert
            ->assertStatus(403);
    }
}
