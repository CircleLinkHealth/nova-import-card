<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit\CallSchedulingAlgo;

use App\Algorithms\Calls\NextCallSuggestor\Handlers\SuccessfulCall;
use App\Algorithms\Calls\NextCallSuggestor\Suggestor;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Traits\PracticeHelpers;
use CircleLinkHealth\Customer\Traits\TimeHelpers;
use CircleLinkHealth\SharedModels\Entities\Call;
use CircleLinkHealth\Core\Tests\TestCase;

class SuccessfulCallHandlerTest extends TestCase
{
    use \CircleLinkHealth\Customer\Traits\UserHelpers;
    use PracticeHelpers;
    use TimeHelpers;
    protected $location;
    protected $practice;

    /**
     * @var \CircleLinkHealth\Customer\Entities\User
     */
    private $nurse;

    public function setUp(): void
    {
        parent::setUp();

        $this->practice = $this->setupPractice(true, true, true, true);
        $this->nurse    = $this->createUser($this->practice->id, 'care-center');
        auth()->login($this->nurse);
    }

    public function fakePatient(Carbon $called)
    {
        $patient = $this->setupPatient($this->practice);

        $patient->patientSummaries()->updateOrCreate([
            'month_year' => $called->copy()->startOfMonth(),
        ], [
            'bhi_time'               => 0,
            'ccm_time'               => 1300,
            'total_time'             => 1300,
            'no_of_calls'            => 5,
            'no_of_successful_calls' => 2,
        ]);

        $patient->inboundCalls()->create([
            'status'          => Call::REACHED,
            'called_date'     => $called->copy()->subDay()->toDateTimeString(),
            'outbound_cpm_id' => $this->nurse->id,
        ]);

        $patient->inboundCalls()->create([
            'status'          => Call::REACHED,
            'called_date'     => $called->toDateTimeString(),
            'outbound_cpm_id' => $this->nurse->id,
        ]);

        $this->addTime($this->nurse, $patient, 22, true);

        $patient->patientInfo->attachNewOrDefaultCallWindows();

        return $patient;
    }

    /**
     * Test that a patient who has reached 20 minutes in the first week of the month, and wants to be called more than
     * once will get a scheduled call on the last week of the month.
     */
    public function test_patient_over_20_mins_in_first_week_call_more_than_once()
    {
        $called = Carbon::now()->startOfMonth()->addDay(4);
        Carbon::setTestNow($called);
        $patient = $this->fakePatient($called);

        $patient->patientInfo->preferred_calls_per_month = 3;
        $patient->patientInfo->save();

        Carbon::setTestNow(now()->startOfMonth()->addDays(10));
        $prediction = (new Suggestor())->handle($patient, new SuccessfulCall());

        $this->assertNotEmpty($prediction);

        $this->assertTrue($prediction->date <= $called->copy()->endOfMonth()->toDateString() && $prediction->date >= $called->copy()->endOfMonth()->subWeek()->toDateString());
    }

    /**
     * Test that a patient who has reached 20 minutes in the fourth week of the month, and wants to be called more than
     * once will get a scheduled call next month.
     */
    public function test_patient_over_20_mins_in_fourth_week_call_more_than_once()
    {
        $called = Carbon::now()->endOfMonth()->subWeek()->addDays(3);
        Carbon::setTestNow($called);
        $patient = $this->fakePatient($called);

        $patient->patientInfo->preferred_calls_per_month = 2;
        $patient->patientInfo->save();

        $prediction = (new Suggestor())->handle($patient, new SuccessfulCall());

        $this->assertNotEmpty($prediction);

        $this->assertTrue($prediction->date <= $called->copy()->addMonth()->endOfMonth()->subWeek(2)->toDateString() && $prediction->date > $called->copy()->addWeek()->toDateString());
    }

    /**
     * Test that a patient who has reached 20 minutes in the second week of the month, and wants to be called once a
     * month will get a scheduled call next month.
     */
    public function test_patient_over_20_mins_in_second_week_call_once()
    {
        $called = Carbon::now()->startOfMonth()->addWeek()->addDay(2);
        Carbon::setTestNow($called);
        $patient = $this->fakePatient($called);

        $patient->patientInfo->preferred_calls_per_month = 1;

        $prediction = (new Suggestor())->handle($patient, new SuccessfulCall());

        $this->assertNotEmpty($prediction);

        $this->assertTrue($prediction->date > $called->copy()->endOfMonth()->toDateString() && $prediction->date <= $called->copy()->addMonth()->endOfMonth()->toDateString());
    }

    /**
     * Test that a patient who has reached 20 minutes in the third week of the month, and wants to be called once a
     * month will get a scheduled call on next month.
     */
    public function test_patient_over_20_mins_in_third_week_call_once()
    {
        $called = Carbon::now()->endOfMonth()->subWeeks(2)->addDay(4);
        Carbon::setTestNow($called);
        $patient = $this->fakePatient($called);

        $patient->patientInfo->preferred_calls_per_month = 1;
        $patient->patientInfo->save();

        $prediction = (new Suggestor())->handle($patient, new SuccessfulCall());

        $this->assertNotEmpty($prediction);

        $this->assertTrue($prediction->date > $called->copy()->endOfMonth()->toDateString() && $prediction->date <= $called->copy()->addMonth()->endOfMonth()->subWeek(1)->toDateString());
    }
}
