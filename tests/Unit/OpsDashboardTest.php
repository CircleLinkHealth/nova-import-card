<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Jobs\GenerateOpsDailyPracticeReport;
use App\Repositories\PatientSummaryEloquentRepository;
use App\Traits\Tests\UserHelpers;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\OpsDashboardPracticeReport;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Faker\Factory;
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

    private function runJobToGenerateDBDataForPractice($practiceId)
    {
        //this will produce DB entry with data for given practice
        GenerateOpsDailyPracticeReport::dispatch($practiceId);
    }
}
