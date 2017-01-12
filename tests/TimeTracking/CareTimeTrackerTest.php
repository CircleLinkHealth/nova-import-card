<?php

use App\Activity;
use App\Algorithms\Calls\CallAlgoHelper;
use App\Algorithms\Invoicing\AlternativeCareTimePayableCalculator;
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
            ->first()->patientInfo;
    }

    public function testCareTime()
    {

        //pretends patient has 10 mins of care.
        $pre_ccm = 600;
        $this->patient->cur_month_activity_time = $pre_ccm;

        //creates new activity on 12 mins.
        $this->activity = $this->createActivityForPatientNurse($this->patient, $this->nurse, 720);

        //new ccm time
        $post_ccm = $this->patient->cur_month_activity_time;

        $data = (new AlternativeCareTimePayableCalculator())->adjustCCMPaybleForActivity($this->activity);

        $report = (new AlternativeCareTimePayableCalculator())->createOrIncrementNurseSummary(
            $this->nurse->nurseInfo, $data['toAddToAccuredTowardsCCM'], $data['toAddToAccuredAfterCCM'], $this->activity->id);

        dd($data);

        $this->assertTrue($report->accrued_towards_ccm == 720, true);
    }
}
