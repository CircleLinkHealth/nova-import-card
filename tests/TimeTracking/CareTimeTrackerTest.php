<?php

use Tests\TestCase;
use App\Activity;
use App\Algorithms\Calls\CallAlgoHelper;
use App\Algorithms\Invoicing\AlternativeCareTimePayableCalculator;
use App\NurseMonthlySummary;
use App\User;
use Carbon\Carbon;
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
    private $date;

    public function setUp()
    {
        parent::setUp();

        $this->nurse = User::find(2726); //test nurse

        $this->date = Carbon::parse(Carbon::now()->firstOfMonth()->format('Y-m-d'));
        $report = NurseMonthlySummary::where('nurse_id', $this->nurse->nurseInfo->id)->where('month_year', $this->date)->first();
        $report ? $report->delete() : '';

        $this->patient = User::ofType('participant')
            ->with('patientInfo')
            ->intersectPracticesWith($this->nurse)
            ->first()->patientInfo;
    }

    public function testCareTime()
    {

        //pretends patient has 45 mins of care.
        $pre_ccm = 4800;
        //gets 20 mins more
        $care_given = 1200;
        $this->patient->cur_month_activity_time = $pre_ccm;

        $report_seed_after = 1200;
        $report_seed_towards = 3600;

        //create scenario where patient has been given care by only this nurse
        $report = NurseMonthlySummary::create([

            'nurse_id' => $this->nurse->nurseInfo->id,
            'month_year' => $this->date,
            'accrued_after_ccm' => $report_seed_after,
            'accrued_towards_ccm' => $report_seed_towards,
            'no_of_calls' => 0,
            'no_of_successful_calls' => 0
        ]);

        //toggle patient complex ccm
        $record = $this->makePatientMonthlyRecord($this->patient);
        $record->is_ccm_complex = true;
        $record->save();

        //creates new activity on 12 mins.
        $this->activity = $this->createActivityForPatientNurse($this->patient, $this->nurse, $care_given);

        $calculator = (new AlternativeCareTimePayableCalculator($this->nurse->nurseInfo));

        $data = $calculator->adjustCCMPaybleForActivity($this->activity);

        $report = $calculator->createOrIncrementNurseSummary(
            $data['toAddToAccuredTowardsCCM'],
            $data['toAddToAccuredAfterCCM'],
            $this->activity->id
        );

        $post_ccm = $this->patient->cur_month_activity_time;
        $data['is_ccm_complex'] = $record->is_ccm_complex;
        $data['ccm_before_activity'] = $pre_ccm;
        $data['new_activity_duration'] = $care_given;
        $data['ccm_after_activity'] = $post_ccm;
        $data['report'] = [

            'high_rate_after' => $report->accrued_towards_ccm,
            'low_rate_after' => $report->accrued_after_ccm,
            'high_rate_before' => $report_seed_towards,
            'low_rate_before' => $report_seed_after

        ];


        dd($data);

        $this->assertTrue(true, true);
    }
}
