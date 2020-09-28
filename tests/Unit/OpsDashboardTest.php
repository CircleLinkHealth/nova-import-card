<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Jobs\GenerateOpsDailyPracticeReport;
use CircleLinkHealth\CpmAdmin\Repositories\PatientSummaryEloquentRepository;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\OpsDashboardPracticeReport;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use Faker\Factory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Artisan;
use Tests\Helpers\CarePlanHelpers;

class OpsDashboardTest extends \Tests\TestCase
{
    use CarePlanHelpers;
    use UserHelpers;

    /**
     * @var Factory
     */
    protected $faker;
    /**
     * @var Location
     */
    protected $location;
    /**
     * @var User
     */
    protected $nurse;
    /**
     * @var User
     */
    protected $patient;

    /**
     * @var Practice
     */
    protected $practice;
    /**
     * @var User
     */
    protected $provider;

    /**
     * @var PatientSummaryEloquentRepository
     */
    protected $repo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        $this->practice = factory(Practice::class)->create();
        $this->location = Location::firstOrCreate([
            'practice_id' => $this->practice->id,
        ]);

        $this->provider = $this->createUser($this->practice->id);
        $this->nurse    = $this->createUser($this->practice->id, 'care-center');

        $this->patient = $this->setupPatient($this->practice);
    }

    public function test_deltas_are_accurate()
    {
        //setup practice and patients
        $practice = factory(Practice::class)->create();

        $patients = [];
        for ($i = 10; $i > 0; --$i) {
            $patients[] = $this->setupPatient($practice);
        }

        //generate report for 1 day
        $twoDaysAgo = Carbon::now()->setTimeFromTimeString('23:30')->subDay(2);
        Carbon::setTestNow($twoDaysAgo);
        $this->runJobToGenerateDBDataForPractice($practice->id, $twoDaysAgo);

        $dbReport1 = OpsDashboardPracticeReport::where('practice_id', $practice->id)
            ->where('date', $twoDaysAgo->toDateString())
            ->first();
        //assert true numbers

        $this->assertNotNull($dbReport1);

        $dbReport1Data = $dbReport1->data;
        $this->assertTrue( ! empty($dbReport1Data));
        $this->assertTrue(array_keys_exist([
            'total_paused_count',
            'total_unreachable_count',
            'total_withdrawn_count',
            'prior_day_report_updated_at',
            'report_updated_at',
            'enrolled_patient_ids',
        ], $dbReport1Data));
        $this->assertTrue(10 === $dbReport1Data['Added']);
        $this->assertTrue(0 === $dbReport1Data['Paused']);
        $this->assertTrue(0 === $dbReport1Data['Withdrawn']);
        $this->assertTrue(0 === $dbReport1Data['Unreachable']);
        $this->assertTrue(10 === $dbReport1Data['Delta']);
        $this->assertTrue(10 === $dbReport1Data['Total']);

        //change patient statuses
        $patient0                          = $patients[0];
        $patient0->patientInfo->ccm_status = Patient::PAUSED;
        $patient0->save();

        $patient1                          = $patients[1];
        $patient1->patientInfo->ccm_status = Patient::WITHDRAWN_1ST_CALL;
        $patient1->save();

        $patient2                          = $patients[2];
        $patient2->patientInfo->ccm_status = Patient::UNREACHABLE;
        $patient2->save();

        $patient3             = $patients[3];
        $patient3->first_name = 'Traxton';
        $patient3->save();
        $patient3->delete();

        //add new patient
        $this->setupPatient($practice);

        //generate report for next day
        Carbon::setTestNow();
        $yesterday = Carbon::now()->subDay(1)->setTimeFromTimeString('23:30');
        Carbon::setTestNow($yesterday);
        $this->runJobToGenerateDBDataForPractice($practice->id, $yesterday);

        //assert deltas are correct
        $dbReport2 = OpsDashboardPracticeReport::where('practice_id', $practice->id)
            ->where('date', $yesterday->toDateString())
            ->first();
        //assert true numbers

        $this->assertNotNull($dbReport2);

        $dbReport2Data = $dbReport2->data;
        $this->assertTrue( ! empty($dbReport2Data));
        $this->assertTrue(array_keys_exist([
            'total_paused_count',
            'total_unreachable_count',
            'total_withdrawn_count',
            'prior_day_report_updated_at',
            'report_updated_at',
            'enrolled_patient_ids',
        ], $dbReport2Data));
        $this->assertTrue(1 === $dbReport2Data['Added']);
        $this->assertTrue(1 === $dbReport2Data['Paused']);
        $this->assertTrue(1 === $dbReport2Data['Withdrawn']);
        $this->assertTrue(1 === $dbReport2Data['Unreachable']);
        $this->assertTrue(1 === $dbReport2Data['Deleted']);
        $this->assertTrue(-3 === $dbReport2Data['Delta']);
        $this->assertTrue($dbReport2Data['Prior Day totals'] === $dbReport1Data['Total']);
        $this->assertTrue($dbReport1Data['Total'] + $dbReport2Data['Delta'] === $dbReport2Data['Total']);

        //change patient statuses yet again, make sure to add patients that were previously enrolled to assert 'Unique Added' count.
        $patient0                          = $patients[0];
        $patient0->patientInfo->ccm_status = Patient::ENROLLED;
        $patient0->save();

        //add new patient
        $this->setupPatient($practice);

        //generate report for next day
        Carbon::setTestNow();
        $today = Carbon::now()->setTimeFromTimeString('23:30');

        $this->runJobToGenerateDBDataForPractice($practice->id, $today);

        //assert deltas are correct
        $dbReport3 = OpsDashboardPracticeReport::where('practice_id', $practice->id)
            ->where('date', $today->toDateString())
            ->first();
        //assert true numbers

        $this->assertNotNull($dbReport3);

        $dbReport3Data = $dbReport3->data;
        $this->assertTrue( ! empty($dbReport3Data));
        $this->assertTrue(array_keys_exist([
            'total_paused_count',
            'total_unreachable_count',
            'total_withdrawn_count',
            'prior_day_report_updated_at',
            'report_updated_at',
            'enrolled_patient_ids',
        ], $dbReport3Data));

        $this->assertTrue(2 === $dbReport3Data['Added']);

        //if current date is start of month, all added patients will be considered unique added
        $expectedUniqueAdded = $today->toDateString() === $today->copy()->startOfMonth()->toDateString() ? 2 : 1;
        $this->assertTrue($expectedUniqueAdded === $dbReport3Data['Unique Added']);

        $this->assertTrue(2 === $dbReport3Data['Delta']);
        $this->assertTrue($dbReport3Data['Prior Day totals'] === $dbReport2Data['Total']);
        $this->assertTrue($dbReport2Data['Total'] + $dbReport3Data['Delta'] === $dbReport3Data['Total']);
    }

    public function test_patient_ccm_status_revisions_are_stored()
    {
        $initialStatus                          = $this->patient->patientInfo->ccm_status;
        $this->patient->patientInfo->ccm_status = Patient::PAUSED;
        $this->patient->patientInfo->save();
        sleep(2);
        /**
         * @var Collection
         * */
        $revisions = $this->patient->patientInfo->patientCcmStatusRevisions()
            ->ofDate(Carbon::today())
            ->get();

        $this->assertTrue($revisions->isNotEmpty());

        $latestRevision = $revisions->sortBy('created_at')->last();
        $this->assertEquals($latestRevision->new_value, Patient::PAUSED);
        $this->assertEquals($latestRevision->old_value, $initialStatus);
    }

    /**
     * @return void
     */
    public function test_report_is_logged_in_db_for_multiple_practices()
    {
        $practice1 = factory(Practice::class)->create();
        $this->setupPatient($practice1);

        $practice2 = factory(Practice::class)->create();
        $this->setupPatient($practice2);

        $this->runCommandToGenerateEntireOpsDailyReport();

        $dbReportThis = OpsDashboardPracticeReport::where('practice_id', $this->practice->id)
            ->where('date', Carbon::now()->toDateString())
            ->first();

        $this->assertNotNull($dbReportThis);

        $data = $dbReportThis->data;

        //patient created does not have summary -> thus they should be marked as 0 mins.
        $this->assertEquals($data['0 mins'], 1);
        $this->assertEquals($data['Total'], 1);
        $this->assertEquals($data['total_ccm_time'], 0);
        $this->assertTrue(1 === $dbReportThis->is_processed);

        $dbReport1 = OpsDashboardPracticeReport::where('practice_id', $practice1->id)
            ->where('date', Carbon::now()->toDateString())
            ->first();

        $this->assertNotNull($dbReport1);

        $data1 = $dbReport1->data;

        //patient created does not have summary -> thus they should be marked as 0 mins.
        $this->assertEquals($data1['0 mins'], 1);
        $this->assertEquals($data1['Total'], 1);
        $this->assertEquals($data1['total_ccm_time'], 0);
        $this->assertTrue(1 === $dbReport1->is_processed);

        $dbReport2 = OpsDashboardPracticeReport::where('practice_id', $practice2->id)
            ->where('date', Carbon::now()->toDateString())
            ->first();

        $this->assertNotNull($dbReport2);

        $data1 = $dbReport2->data;

        //patient created does not have summary -> thus they should be marked as 0 mins.
        $this->assertEquals($data1['0 mins'], 1);
        $this->assertEquals($data1['Total'], 1);
        $this->assertEquals($data1['total_ccm_time'], 0);
        $this->assertTrue(1 === $dbReport2->is_processed);
    }

    /**
     * A basic unit test example.
     */
    public function test_report_is_logged_in_db_for_single_practice()
    {
        $this->runCommandToGenerateEntireOpsDailyReport();

        $dbReport = OpsDashboardPracticeReport::where('practice_id', $this->practice->id)
            ->where('date', Carbon::now()->toDateString())
            ->first();

        $this->assertNotNull($dbReport);

        $data = $dbReport->data;

        //patient created does not have summary -> thus they should be marked as 0 mins.
        $this->assertEquals($data['0 mins'], 1);
        $this->assertEquals($data['Total'], 1);
        $this->assertEquals($data['total_ccm_time'], 0);
        $this->assertTrue(1 === $dbReport->is_processed);
    }

    /**
     * @return void
     */
    public function test_report_is_not_logged_in_db_for_single_practice_with_no_enrolled_patients()
    {
        $newPractice = factory(Practice::class)->create();

        $this->runCommandToGenerateEntireOpsDailyReport();

        $dbReport = OpsDashboardPracticeReport::where('practice_id', $newPractice->id)
            ->where('date', Carbon::now()->toDateString())
            ->first();

        $this->assertNull($dbReport);
    }

    private function runCommandToGenerateEntireOpsDailyReport(Carbon $date = null)
    {
        //this will first take all eligible practices and run
        Artisan::call(
            'report:OpsDailyReport',
            [
                'endDate' => $date ? $date->format('Y-m-d') : null,
            ]
        );
    }

    private function runJobToGenerateDBDataForPractice($practiceId, $date = null)
    {
        //this will produce DB entry with data for given practice
        GenerateOpsDailyPracticeReport::dispatch($practiceId, $date);
    }
}
