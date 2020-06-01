<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit\CallSchedulingAlgo;

use App\Algorithms\Calls\SuccessfulHandler;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use Tests\TestCase;

class SuccessfulCallHandlerTest extends TestCase
{
    use \App\Traits\Tests\UserHelpers;

    /**
     * @var \CircleLinkHealth\Customer\Entities\User
     */
    private $nurse;
    private $practice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->practice = factory(Practice::class)->create();
        $this->nurse    = $this->createUser($this->practice->id, 'care-center');
        auth()->login($this->nurse);
    }

    public function fakePatient(Carbon $called)
    {
        $patient = $this->createUser($this->practice->id, 'participant');

        $patient->patientSummaries()->updateOrCreate([
            'month_year' => $called->startOfMonth(),
        ], [
            'bhi_time'               => 0,
            'ccm_time'               => 1300,
            'total_time'             => 1300,
            'no_of_calls'            => 5,
            'no_of_successful_calls' => 2,
        ]);

        $patient->inboundCalls()->create([
            'status'          => 'scheduled',
            'called_date'     => $called->toDateString(),
            'outbound_cpm_id' => $this->nurse->id,
        ]);

        $patient->patientInfo->attachNewOrDefaultCallWindows();

        return $patient;
    }

    /**
     * Test that a patient who has reached 20 minutes in the first week of the month, and wants to be called more than
     * once will get a scheduled call on the last week of the month.
     */
    public function test_patient_over_20_mins_in_first_week_call_more_than_once()
    {
        $called  = Carbon::now()->startOfMonth()->addDay(4);
        $patient = $this->fakePatient($called);

        $patient->patientInfo->preferred_calls_per_month = 3;
        $patient->patientInfo->save();

        Carbon::setTestNow(now()->startOfMonth()->addDays(10));
        $prediction = (new SuccessfulHandler($patient->patientInfo, $called, $patient->inboundCalls->first()))
            ->handle();

        $this->assertNotEmpty($prediction);

        $this->assertTrue($prediction['date'] <= $called->copy()->endOfMonth()->toDateString() && $prediction['date'] >= $called->copy()->endOfMonth()->subWeek()->toDateString());
    }

    /**
     * Test that a patient who has reached 20 minutes in the fourth week of the month, and wants to be called more than
     * once will get a scheduled call next month.
     */
    public function test_patient_over_20_mins_in_fourth_week_call_more_than_once()
    {
        $called  = Carbon::now()->endOfMonth()->subWeek()->addDays(3);
        $patient = $this->fakePatient($called);

        $prediction = (new SuccessfulHandler($patient->patientInfo, $called, $patient->inboundCalls->first()))
            ->handle();

        $this->assertNotEmpty($prediction);

        $this->assertTrue($prediction['date'] <= $called->copy()->addMonth()->endOfMonth()->subWeek(2)->toDateString() && $prediction['date'] > $called->copy()->addWeek()->toDateString());
    }

    /**
     * Test that a patient who has reached 20 minutes in the second week of the month, and wants to be called once a
     * month will get a scheduled call next month.
     */
    public function test_patient_over_20_mins_in_second_week_call_once()
    {
        $called  = Carbon::now()->startOfMonth()->addWeek()->addDay(2);
        $patient = $this->fakePatient($called);

        $patient->patientInfo->preferred_calls_per_month = 1;

        $prediction = (new SuccessfulHandler($patient->patientInfo, $called, $patient->inboundCalls->first()))
            ->handle();

        $this->assertNotEmpty($prediction);

        $this->assertTrue($prediction['date'] > $called->copy()->endOfMonth()->toDateString() && $prediction['date'] <= $called->copy()->addMonth()->endOfMonth()->toDateString());
    }

    /**
     * Test that a patient who has reached 20 minutes in the third week of the month, and wants to be called once a
     * month will get a scheduled call on next month.
     */
    public function test_patient_over_20_mins_in_third_week_call_once()
    {
        $called  = Carbon::now()->endOfMonth()->subWeeks(2)->addDay(4);
        $patient = $this->fakePatient($called);

        $patient->patientInfo->preferred_calls_per_month = 1;

        $prediction = (new SuccessfulHandler($patient->patientInfo, $called, $patient->inboundCalls->first()))
            ->handle();

        $this->assertNotEmpty($prediction);

        $this->assertTrue($prediction['date'] > $called->copy()->endOfMonth()->toDateString() && $prediction['date'] <= $called->copy()->addMonth()->endOfMonth()->subWeek(2)->toDateString());
    }
}
