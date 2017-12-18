<?php

namespace Tests\Unit;

use App\Models\CCD\Problem;
use App\PatientMonthlySummary;
use App\Practice;
use App\Services\ApproveBillablePatientsService;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;

class BillablePatientsServiceTest extends TestCase
{
    use DatabaseTransactions,
        UserHelpers,
        WithFaker;

    private $practice;
    private $patient;
    private $service;

    /**
     * This test assumes that the patient has billable and non-billable ccd problems.
     * In this case, the billable ccd problems will be selected for the summary.
     */
    public function test_it_selects_billable_ccd_problem_without_cpm_problem_id()
    {
        //Set up
        $problem1 = $this->createProblem(true, 33);
        $problem2 = $this->createProblem(true);
        $problem3 = $this->createProblem(false, 0);
        $problem4 = $this->createProblem(false, 2);
        $summary  = $this->createMonthlySummary($this->patient, Carbon::now(), 1400);

        //Run
        $list = $this->service->patientsToApprove($this->practice->id, Carbon::now());

        //Assert
        $this->assertMonthlySummary($summary, $problem1, $problem2, $list);
    }

    /**
     * This test assumes that the patient has 0 problems.
     * In this case, the summary should return null for both problems.
     */
    public function test_it_summary_problems_are_null_if_no_billable_problems()
    {
        //Set up
        $summary  = $this->createMonthlySummary($this->patient, Carbon::now(), 1400);

        //Run
        $list = $this->service->patientsToApprove($this->practice->id, Carbon::now());

        //Assert
        $summary  = $summary->fresh();

        $this->assertTrue($list->count() == 1);

        $row = $list->first();

        $this->assertEquals($row['report_id'], $summary->id);
        $this->assertEquals($row['practice'], $this->practice->display_name);
        $this->assertEquals($row['ccm'], round($summary->ccm_time / 60, 2));
        $this->assertEquals($row['problem1'], null);
        $this->assertEquals($row['problem1_code'], null);
        $this->assertEquals($row['problem2'], null);
        $this->assertEquals($row['problem2_code'], null);
    }

    /**
     * Create a CCD\Problem
     *
     * @param null $billable
     * @param null $cpmProblemId
     *
     * @return mixed
     */
    private function createProblem($billable = null, $cpmProblemId = null)
    {
        return $this->service->storeCcdProblem($this->patient, [
            'name'             => $this->faker->name,
            'billable'         => $billable,
            'cpm_problem_id'   => $cpmProblemId,
            'code'             => $this->faker->bankAccountNumber,
            'code_system_name' => 'ICD-10',
            'code_system_oid'  => '2.16.840.1.113883.6.3',
        ]);
    }

    /**
     * Create a patient monthly summary
     *
     * @param User $patient
     * @param Carbon $monthYear
     * @param $ccmTime
     *
     * @return PatientMonthlySummary
     */
    private function createMonthlySummary(User $patient, Carbon $monthYear, $ccmTime)
    {
        return $patient->patientSummaries()->create([
            'month_year' => $monthYear->startOfMonth()->toDateString(),
            'ccm_time'   => $ccmTime,
        ]);
    }

    /**
     * Assert patient monthly summary
     *
     * @param PatientMonthlySummary $summary
     * @param Problem $problem1
     * @param Problem $problem2
     * @param Collection $list
     */
    private function assertMonthlySummary(
        PatientMonthlySummary $summary,
        Problem $problem1,
        Problem $problem2,
        Collection $list
    ) {
        $summary  = $summary->fresh();
        $problem1 = $problem1->fresh();
        $problem2 = $problem2->fresh();

        $this->assertTrue($list->count() == 1);

        $row = $list->first();

        $this->assertEquals($row['report_id'], $summary->id);
        $this->assertEquals($row['practice'], $this->practice->display_name);
        $this->assertEquals($row['ccm'], round($summary->ccm_time / 60, 2));
        $this->assertEquals($row['problem1'], $problem1->name);
        $this->assertEquals($row['problem1_code'], $problem1->icd10Code());
        $this->assertEquals($row['problem2'], $problem2->name);
        $this->assertEquals($row['problem2_code'], $problem2->icd10Code());

        $this->assertTrue((boolean)$problem1->billable);
        $this->assertTrue((boolean)$problem2->billable);

        $this->assertTrue($this->patient->ccdProblems()->whereBillable(true)->count() == 2);
    }

    /**
     * This test assumes that the patient has 2 billable ccd problems, which should be selected for the monthly summary.
     */
    public function test_it_selects_billable_ccd_problems()
    {
        //Set up
        $problem1 = $this->createProblem(true, 33);
        $problem2 = $this->createProblem();
        $problem3 = $this->createProblem(null, 0);
        $problem4 = $this->createProblem(false, 2);
        $summary  = $this->createMonthlySummary($this->patient, Carbon::now(), 1400);

        //Run
        $list = $this->service->patientsToApprove($this->practice->id, Carbon::now());

        //Assert
        $this->assertMonthlySummary($summary, $problem1, $problem2, $list);
    }

    /**
     * This test assumes that the patient has no billable ccd problems, but has cpm problems.
     * In this case billable ccd problems have to be created from cpm problems, and they should be set to billable.
     */
    public function test_it_creates_billable_ccd_problems_from_cpm_problems()
    {
        $this->patient->cpmProblems()->attach(2);
        $this->patient->cpmProblems()->attach(7);

        $summary  = $this->createMonthlySummary($this->patient, Carbon::now(), 1400);

        //Run
        $list = $this->service->patientsToApprove($this->practice->id, Carbon::now());

        $summary  = $summary->fresh();
        $problem1 = $summary->billableProblem1;
        $problem2 = $summary->billableProblem2;

        $this->assertTrue(in_array($problem1->cpm_problem_id, [2,7]));
        $this->assertTrue(in_array($problem2->cpm_problem_id, [2,7]));

        //Assert
        $this->assertMonthlySummary($summary, $problem1, $problem2, $list);
    }

    /**
     * This test assumes that the patient has 1 billable ccd problem, and cpm problems.
     * In this case, the billable ccd problem will be selected, and a new billable problem will be created from a cpm problem.
     */
    public function test_it_creates_billable_ccd_problem_from_cpm_problem_and_selects_billable_problem()
    {
        $this->patient->cpmProblems()->attach(2);

        $problem1 = $this->createProblem(true, 33);

        $summary  = $this->createMonthlySummary($this->patient, Carbon::now(), 1400);

        //Run
        $list = $this->service->patientsToApprove($this->practice->id, Carbon::now());

        $summary  = $summary->fresh();
        $problem1 = $summary->billableProblem1;
        $problem2 = $summary->billableProblem2;

        $this->assertTrue($problem1->cpm_problem_id == 33);
        $this->assertTrue($problem2->cpm_problem_id == 2);

        //Assert
        $this->assertMonthlySummary($summary, $problem1, $problem2, $list);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->practice = factory(Practice::class)->create();
        $this->patient  = $this->createUser($this->practice->id, 'participant');
        $this->service  = app(ApproveBillablePatientsService::class);
    }
}
