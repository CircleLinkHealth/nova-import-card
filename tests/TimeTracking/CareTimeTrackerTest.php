<?php

use App\Activity;
use App\Algorithms\Calls\CallAlgoHelper;
use App\NurseMonthlySummary;
use App\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\TimeTrackingHelpers;
use Tests\Helpers\UserHelpers;

class CareTimeTrackerTest extends TestCase
{
    use UserHelpers, CallAlgoHelper, TimeTrackingHelpers;

    private $patient;
    private $nurse;
    private $activity;

    public function setUp()
    {
        parent::setUp();

        $this->nurse = User::find(2726); //test nurse

        $this->patient = User::ofType('participant')
            ->with('patientInfo')
            ->intersectPracticesWith($this->nurse)
            ->first();
    }

    public function testCareTime()
    {

        $pre_ccm = $this->patient->patientInfo->cur_month_activity_time = 600;
        $this->activity = $this->createActivityForPatientNurse($this->patient, $this->nurse, 10);

        $post_ccm = $this->patient->patientInfo->cur_month_activity_time;

        $report = (new NurseMonthlySummary())->createOrIncrementNurseSummary(
            $this->nurse->nurseInfo, 100, 100);

        $data = (new NurseMonthlySummary())->adjustCCMPaybleForActivity($this->activity);

        dd($data);

        $this->assertTrue(is_object($this->activity), true);
    }
}
