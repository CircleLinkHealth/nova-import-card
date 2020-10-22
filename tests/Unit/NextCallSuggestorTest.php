<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Algorithms\Calls\NextCallSuggestor\Handlers\SuccessfulCall;
use App\Algorithms\Calls\NextCallSuggestor\Suggestion;
use App\Algorithms\Calls\NextCallSuggestor\Suggestor;
use App\Algorithms\Calls\NurseFinder\NurseFinderEloquentRepository;
use App\Contracts\CallHandler;
use App\Traits\Tests\PracticeHelpers;
use Carbon\Carbon;
use CircleLinkHealth\Customer\AppConfig\StandByNurseUser;
use CircleLinkHealth\Customer\Entities\PatientContactWindow;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use Mockery;
use Tests\TestCase;

class NextCallSuggestorTest extends TestCase
{
    use PracticeHelpers;
    use UserHelpers;
    protected $location;
    protected $patient;

    protected $practice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->practice = $this->setupPractice(true, true, true, true);
        $this->patient  = $this->setupPatient($this->practice, true);
    }

    public function test_it_returns_patient_associated_nurse_in_1st_week_of_month()
    {
        Carbon::setTestNow(now()->startOfMonth());
        $this->itShouldReturnPatientAssociatedNurse();
    }

    public function test_it_returns_patient_associated_nurse_in_2nd_week_of_month()
    {
        Carbon::setTestNow(now()->startOfMonth()->addWeeks());
        $this->itShouldReturnPatientAssociatedNurse();
    }

    public function test_it_returns_patient_associated_nurse_in_3rd_week_of_month()
    {
        Carbon::setTestNow(now()->startOfMonth()->addWeeks(2));
        $this->itShouldReturnPatientAssociatedNurse();
    }

    public function test_it_returns_patient_associated_nurse_in_4th_week_of_month()
    {
        Carbon::setTestNow(now()->startOfMonth()->addWeeks(3));
        $this->itShouldReturnPatientAssociatedNurse();
    }

    public function test_it_returns_standby_nurse_if_patient_doesnt_have_associated_nurse_and_standby_nurse_is_set()
    {
        $nurse = factory(User::class)->make([
            'id'           => 123456789,
            'display_name' => 'Soulla Masoulla',
        ]);
        $this->mock(StandByNurseUser::class, function ($mock) use ($nurse) {
            $mock->shouldReceive('user')->atLeast(1)->andReturn($nurse);
        });

        $suggestion = (new Suggestor())->handle($this->patient, $handler = new SuccessfulCall());

        self::assertValidNurseResponse($suggestion, $nurse, $this->patient, $handler);
    }

    public function test_nurse_is_null_if_patient_doesnt_have_associated_nurse_and_standby_nurse_is_not_set()
    {
        $repo = Mockery::mock(NurseFinderEloquentRepository::class);

        $repo->shouldReceive('find')
            ->with($this->patient->id)
            ->once()
            ->andReturn(null);

        $this->instance(NurseFinderEloquentRepository::class, $repo);

        $suggestion = (new Suggestor())->handle($this->patient, $handler = new SuccessfulCall());

        self::assertNullResponse($suggestion, $this->patient, $handler);
    }

    private static function assertNullResponse(Suggestion $suggestion, User $patient, CallHandler $handler)
    {
        self::assertTrue($suggestion instanceof Suggestion);
        self::assertNull($suggestion->nurse);
        self::assertEquals('', $suggestion->attempt_note);
        self::assertEquals(false, $suggestion->ccm_time_achieved);
        self::assertEquals(false, $suggestion->ccm_above);
        self::assertEquals('00:00:00', $suggestion->formatted_monthly_time);
        self::assertEquals('Call patient after a week', $suggestion->logic);
        if (now()->addWeek()->isCurrentMonth()) {
            self::assertTrue($suggestion->nextCallDate->isCurrentMonth());
            self::assertTrue(Carbon::parse($suggestion->date)->isCurrentMonth());
        } else {
            self::assertTrue($suggestion->nextCallDate->isNextMonth());
            self::assertTrue(Carbon::parse($suggestion->date)->isNextMonth());
        }
        self::assertEquals(0, $suggestion->no_of_successful_calls);
        self::assertNull($suggestion->nurse_display_name);
        self::assertEquals($patient, $suggestion->patient);
        self::assertEquals($handler->createSchedulerInfoString($suggestion), $suggestion->predicament);
        self::assertEquals(true, $suggestion->successful);
        self::assertEquals(
            Carbon::parse(PatientContactWindow::DEFAULT_WINDOW_TIME_END)->format('H:i'),
            Carbon::parse($suggestion->window_end)->format('H:i')
        );
        self::assertEquals('This patient has no assigned nurse in CPM.', $suggestion->window_match);
        self::assertEquals(
            Carbon::parse(PatientContactWindow::DEFAULT_WINDOW_TIME_START)->format('H:i'),
            Carbon::parse($suggestion->window_start)->format('H:i')
        );
        self::assertEquals(0, $suggestion->ccm_time_in_seconds);
    }

    private static function assertValidNurseResponse(Suggestion $suggestion, User $nurse, User $patient, CallHandler $handler)
    {
        self::assertTrue($suggestion instanceof Suggestion);
        self::assertTrue($suggestion->nurse === $nurse->id);
        self::assertEquals('', $suggestion->attempt_note);
        self::assertEquals(false, $suggestion->ccm_time_achieved);
        self::assertEquals(false, $suggestion->ccm_above);
        self::assertEquals('00:00:00', $suggestion->formatted_monthly_time);
        self::assertEquals('Call patient after a week', $suggestion->logic);
        if (now()->addWeek()->isCurrentMonth()) {
            self::assertTrue($suggestion->nextCallDate->isCurrentMonth());
            self::assertTrue(Carbon::parse($suggestion->date)->isCurrentMonth());
        } else {
            self::assertTrue($suggestion->nextCallDate->isNextMonth());
            self::assertTrue(Carbon::parse($suggestion->date)->isNextMonth());
        }
        self::assertEquals(0, $suggestion->no_of_successful_calls);
        self::assertEquals($nurse->id, $suggestion->nurse);
        self::assertEquals($nurse->display_name, $suggestion->nurse_display_name);
        self::assertEquals($patient, $suggestion->patient);
        self::assertEquals($handler->createSchedulerInfoString($suggestion), $suggestion->predicament);
        self::assertEquals(true, $suggestion->successful);
        self::assertEquals(
            Carbon::parse(PatientContactWindow::DEFAULT_WINDOW_TIME_END)->format('H:i'),
            Carbon::parse($suggestion->window_end)->format('H:i')
        );
        self::assertEquals("Assigning next call to $nurse->display_name.", $suggestion->window_match);
        self::assertEquals(
            Carbon::parse(PatientContactWindow::DEFAULT_WINDOW_TIME_START)->format('H:i'),
            Carbon::parse($suggestion->window_start)->format('H:i')
        );
        self::assertEquals(0, $suggestion->ccm_time_in_seconds);
    }

    private function itShouldReturnPatientAssociatedNurse()
    {
        $nurse = factory(User::class)->make([
            'id' => rand(1, 9999999),
        ]);

        $repo = Mockery::mock(NurseFinderEloquentRepository::class);

        $repo->shouldReceive('find')
            ->with($this->patient->id)
            ->once()
            ->andReturn($nurse);

        $this->instance(NurseFinderEloquentRepository::class, $repo);

        $suggestion = (new Suggestor())->handle($this->patient, $handler = new SuccessfulCall());

        self::assertValidNurseResponse($suggestion, $nurse, $this->patient, $handler);
    }
}
