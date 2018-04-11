<?php

namespace Tests\Unit;

use App\Http\Resources\ApprovableBillablePatient;
use App\Models\CCD\Problem;
use App\PatientMonthlySummary;
use App\Practice;
use App\Services\ApproveBillablePatientsService;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Collection;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;

class BillablePatientsServiceTest extends TestCase
{
    use DatabaseTransactions,
        UserHelpers,
        WithoutMiddleware,
        WithFaker;

    private $practice;
    private $patient;
    private $service;
    private $summary;

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

        //Run
        $list = $this->service->patientsToApprove($this->practice->id, Carbon::now())
                              ->getCollection();

        //Assert
        $this->assertMonthlySummary($this->summary, $problem1, $problem2, $list);
    }

    /**
     * This test assumes that the patient has 0 problems.
     * In this case, the summary should return null for both problems.
     */
    public function test_it_summary_problems_are_null_if_no_billable_problems()
    {
        //Run
        $list = $this->service->patientsToApprove($this->practice->id, Carbon::now())
                              ->getCollection();

        //Assert
        $this->summary = $this->summary->fresh();

        $this->assertTrue($list->count() == 1);

        $row = (new ApprovableBillablePatient($list->first()))->resolve();

        $this->assertEquals($row['report_id'], $this->summary->id);
        $this->assertEquals($row['practice'], $this->practice->display_name);
        $this->assertEquals($row['ccm'], round($this->summary->ccm_time / 60, 2));
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
        return $this->service->patientSummaryRepo->storeCcdProblem($this->patient, [
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
        return $patient->patientSummaries()->updateOrCreate([
            'month_year' => $monthYear->startOfMonth()->toDateString(),
        ], [
            'ccm_time'               => $ccmTime,
            'no_of_successful_calls' => 2,
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

        $row = (new ApprovableBillablePatient($list->first()))->resolve();

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
    public function test_it_sets_patient_without_billable_ccd_problems_to_qa()
    {
        $problems = collect([
            [
                'is_monitored'   => 0,
                'name'           => 'Coronary Heart Disease',
                'billable'       => 0,
                'cpm_problem_id' => 4,
            ],
            [
                'is_monitored' => 0,
                'name'         => 'Aortic regurgitation',
                'billable'     => 0,

            ],
            [
                'is_monitored'   => 1,
                'name'           => 'Atrial Fibrillation',
                'billable'       => 0,
                'cpm_problem_id' => 3,

            ],
            [
                'is_monitored' => 0,
                'name'         => 'Peripheral Vascular Disease',
                'billable'     => 0,
            ],
            [
                'is_monitored' => 0,
                'name'         => 'D V T',
                'billable'     => 0,

            ],
            [
                'is_monitored' => 0,
                'name'         => 'Varicose veins, lower extremities',
                'billable'     => 0,

            ],
            [
                'is_monitored' => 0,
                'name'         => 'Carotid bruit',
                'billable'     => 0,
            ],
            [
                'is_monitored' => 0,
                'name'         => 'Family History of Hyperlipidemia:',
                'billable'     => 0,
            ],
            [
                'is_monitored' => 0,
                'name'         => 'ICD in Situ',
                'billable'     => 0,
            ],
            [
                'is_monitored' => 0,
                'name'         => 'Coronary Stent',
                'billable'     => 0,
            ],
            [
                'is_monitored'   => 1,
                'name'           => 'Afib',
                'billable'       => 0,
                'cpm_problem_id' => 3,

            ],
        ])
            ->map(function ($p) {
                return $this->patient
                    ->ccdProblems()
                    ->create($p);
            });

        //Run
        $list = $this->service->patientsToApprove($this->practice->id, Carbon::now())
                              ->getCollection();

        //Assert
        $this->assertMonthlySummary($this->summary, $problems[0], $problems[2], $list);
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

        //Run
        $list = $this->service->patientsToApprove($this->practice->id, Carbon::now())
            ->getCollection();

        //Assert
        $this->assertMonthlySummary($this->summary, $problem1, $problem2, $list);
    }

    /**
     * This test assumes that the patient has no billable ccd problems, but has cpm problems.
     * In this case billable ccd problems have to be created from cpm problems, and they should be set to billable.
     */
    public function test_it_creates_billable_ccd_problems_from_cpm_problems()
    {
        $this->patient->cpmProblems()->attach(2);
        $this->patient->cpmProblems()->attach(7);

        //Run
        $list = $this->service->patientsToApprove($this->practice->id, Carbon::now())
                              ->getCollection();

        $this->summary = $this->summary->fresh();
        $problem1      = $this->summary->billableProblem1;
        $problem2      = $this->summary->billableProblem2;

        $this->assertTrue(in_array($problem1->cpm_problem_id, [2, 7]));
        $this->assertTrue(in_array($problem2->cpm_problem_id, [2, 7]));

        //Assert
        $this->assertMonthlySummary($this->summary, $problem1, $problem2, $list);
    }

    /**
     * This test assumes that the patient has 1 billable ccd problem, and cpm problems.
     * In this case, the billable ccd problem will be selected, and a new billable problem will be created from a cpm
     * problem.
     */
    public function test_it_creates_billable_ccd_problem_from_cpm_problem_and_selects_billable_problem()
    {
        //set up
        $this->patient->cpmProblems()->attach(2);

        $problem1 = $this->createProblem(true, 33);

        //Run
        $list = $this->service->patientsToApprove($this->practice->id, Carbon::now())
                              ->getCollection();

        $this->summary = $this->summary->fresh();
        $problem1      = $this->summary->billableProblem1;
        $problem2      = $this->summary->billableProblem2;

        $this->assertTrue($problem1->cpm_problem_id == 33);
        $this->assertTrue($problem2->cpm_problem_id == 2);

        //Assert
        $this->assertMonthlySummary($this->summary, $problem1, $problem2, $list);
    }

    public function test_it_stores_ccd_problem_with_cpm_id()
    {
        $uri = route('monthly.billing.store-problem');

        $params = [
            'problem_no'     => 'problem_1',
            'id'             => 'New',
            'name'           => 'Test problem',
            'cpm_problem_id' => '33',
            'code'           => 'code',
            'report_id'      => $this->summary->id,
        ];

        $response = $this->call('POST', $uri, $params);

        $response->assertStatus(200);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->practice = factory(Practice::class)->create();
        $this->patient  = $this->createUser($this->practice->id, 'participant');
        $this->service  = app(ApproveBillablePatientsService::class);
        $this->summary  = $this->createMonthlySummary($this->patient, Carbon::now(), 1400);
    }
}
