<?php

use App\PageTimer;
use App\User;
use Carbon\Carbon;
use Tests\HandlesUsersAndCarePlans;

class TimeTrackingOverlapTest extends TestCase
{
    use HandlesUsersAndCarePlans;

    public function testOverlap()
    {
        $programId = 9;
        $provider = $this->createUser();
        $patient = $this->createUser($programId, 'participant');
        $startTime = '2016-10-11 18:17:00';
        $endTime = '2016-10-11 18:22:00';

        PageTimer::create([
            'billable_duration' => '300',
            'duration' => '300',
            'patient_id' => $patient->ID,
            'provider_id' => $provider->ID,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        //this should have zero billable
        $testStartTime = '2016-10-11 18:18:00';
        $testEndTime = '2016-10-11 18:21:00';
        $testDuration = 180000;
        $this->createTrackingEvent($provider, $patient, $programId, $testDuration, $testStartTime, $testEndTime);

        $this->seeInDatabase('lv_page_timer', [
             'billable_duration' => 0,
             'duration' => $testDuration / 1000,
             'start_time' => $testStartTime,
             'end_time' => $testEndTime,
        ]);


        //this should have 120 billable
        $testStartTime = '2016-10-11 18:16:00';
        $testEndTime = '2016-10-11 18:23:00';
        $testDuration = 420000;
        $this->createTrackingEvent($provider, $patient, $programId, $testDuration, $testStartTime, $testEndTime);

        $this->seeInDatabase('lv_page_timer', [
            'billable_duration' => 120,
            'duration' => $testDuration / 1000,
            'start_time' => $testStartTime,
            'end_time' => $testEndTime,
        ]);


        //this should have 60 billable
        $testStartTime = '2016-10-11 18:16:00';
        $testEndTime = '2016-10-11 18:21:00';
        $testDuration = 300000;
        $this->createTrackingEvent($provider, $patient, $programId, $testDuration, $testStartTime, $testEndTime);

        $this->seeInDatabase('lv_page_timer', [
            'billable_duration' => 0,
            'duration' => $testDuration / 1000,
            'start_time' => $testStartTime,
            'end_time' => $testEndTime,
        ]);


        //this should have 240 billable
        $testStartTime = '2016-10-11 18:18:00';
        $testEndTime = '2016-10-11 18:26:00';
        $testDuration = 480000;
        $this->createTrackingEvent($provider, $patient, $programId, $testDuration, $testStartTime, $testEndTime);

        $this->seeInDatabase('lv_page_timer', [
            'billable_duration' => 240,
            'duration' => $testDuration / 1000,
            'start_time' => $testStartTime,
            'end_time' => $testEndTime,
        ]);
    }

    public function createTrackingEvent(User $provider, User $patient, $programId, $duration, $startTime, $testEndTime)
    {
        $response = $this->call('POST', route('api.pagetracking'), [
            'patientId' => $patient->ID,
            'providerId' => $provider->ID,
            'totalTime' => $duration,
            'programId' => $programId,
            'startTime' => $startTime,
            'testEndTime' => $testEndTime,
            'urlFull' => 'www.url.com',
            'urlShort' => 'url.com',
            'ipAddr' => '1.1.1.1',
            'activity' => 'activity',
            'title' => 'title',
            //I think qs is not needed
            'qs' => '',
        ]);
    }
}
