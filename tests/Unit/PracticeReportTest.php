<?php

namespace Tests\Unit;

use App\Console\Commands\CreateCallsReportForPractice;
use App\Console\Commands\CreatePatientProblemsReportForPractice;
use App\Exports\PracticeReports\PatientProblemsReport;
use App\Exports\PracticeReports\PracticeCallsReport;
use App\Notifications\SendSignedUrlToDownloadPracticeReport;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Notification;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\Helpers\SetupTestCustomerTrait;
use Tests\TestCase;

class PracticeReportTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    use SetupTestCustomerTrait;

    protected $reportClass;
    protected $commandClass;

    public function test_practice_calls_report_class(){
        $this->makeAssertionsForReportClass(PracticeCallsReport::class, CreateCallsReportForPractice::class);
    }

    public function test_patient_problems_report_class(){
        $this->makeAssertionsForReportClass(PatientProblemsReport::class, CreatePatientProblemsReportForPractice::class);
    }

    public function makeAssertionsForReportClass($reportClass, $commandClass)
    {
        $this->reportClass  = $reportClass;
        $this->commandClass = $commandClass;

        $this->consoleCommandSendsNotification();
        $this->modelNotFoundExceptionThrownIfUserPassedDoesNotHaveAccessToPractice();
        $this->notificationIsSent();
        $this->otherUsersFromTheSamePracticeDoNotHaveAccessToTheReport();
    }

    /**
     * We want to test that given the correct input, the command will produce the report.
     *
     * @see PracticeCallsReport
     */
    public function consoleCommandSendsNotification()
    {
        //setup
        $userId     = 1;
        $practiceId = 10;
        $mock       = \Mockery::mock($this->reportClass);

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

        $this->instance($this->reportClass, $mock);

        //test
        $this->artisan($this->commandClass, [
            'practice_id' => $practiceId,
            'user_id'     => $userId,
        ])
             ->assertExitCode(0)
             ->expectsOutput('Command ran.');
    }

    /**
     * This tests a safeguard against trying to create a report for a user who does not have access to the practice.
     */
    public function modelNotFoundExceptionThrownIfUserPassedDoesNotHaveAccessToPractice()
    {
        //setup
        $customer1 = $this->createTestCustomerData(0);
        $customer2 = $this->createTestCustomerData(0);
        $report    = new $this->reportClass();

        try {
            //test
            $report->forPractice($customer1['practice']->id)
                   ->forUser($customer2['admin']->id);
        } catch (\Exception $e) {
            //assert
            $this->assertEquals(ModelNotFoundException::class, get_class($e));
        }
    }

    public function notificationIsSent()
    {
        //setup
        Notification::fake();
        $customer = $this->createTestCustomerData(5);
        $user     = $customer['provider'];
        $practice = $customer['practice'];
        $report   = new $this->reportClass();

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
            SendSignedUrlToDownloadPracticeReport::class,
            function ($notification, $channels, $notifiable) use ($user) {
                $this->assertEquals(['database', 'mail'], $channels);

                return (int)$notifiable->id === (int)$user->id;
            }
        );
    }

    public function otherUsersFromTheSamePracticeDoNotHaveAccessToTheReport()
    {
        //setup
        Notification::fake();
        $customer = $this->createTestCustomerData(1);
        $user     = $customer['provider'];
        $user2    = $customer['admin'];
        $report   = new $this->reportClass();

        //test
        $report->forPractice($customer['practice']->id)
               ->forUser($user->id)
               ->createMedia()
               ->notifyUser();

//        $this->actingAs($user2)->call('get', $report->getSignedLink())
//            //assert
//             ->assertStatus(403);
    }
}
