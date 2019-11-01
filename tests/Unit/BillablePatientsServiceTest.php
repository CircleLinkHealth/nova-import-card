<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Http\Resources\ApprovableBillablePatient;
use App\Models\CCD\Problem;
use App\Models\CPM\CpmProblem;
use App\Repositories\PatientSummaryEloquentRepository;
use App\Services\ApproveBillablePatientsService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Collection;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;

class BillablePatientsServiceTest extends TestCase
{
    use UserHelpers;
    use
        WithFaker;
    use
        WithoutMiddleware;
    private $patient;

    private $practice;

    /**
     * @var PatientSummaryEloquentRepository
     */
    private $repo;

    /**
     * @var ApproveBillablePatientsService
     */
    private $service;
    private $summary;

    protected function setUp()
    {
        parent::setUp();

        $this->practice = factory(Practice::class)->create();
        $this->patient  = $this->createUser($this->practice->id, 'participant');
        $this->service  = app(ApproveBillablePatientsService::class);
        $this->repo     = app(PatientSummaryEloquentRepository::class);
        $this->summary  = $this->createMonthlySummary($this->patient, Carbon::now(), 1400);
    }

    public function test_it_selects_bhi_problems()
    {
        $defaultServices = ChargeableService::defaultServices();

        $this->practice->chargeableServices()->sync($defaultServices->pluck('id')->all());
        $this->summary->bhi_time   = 1300; //over 20 mins
        $this->summary->total_time = $this->summary->bhi_time + $this->summary->ccm_time; //over 20 mins
        $this->summary->save();

        //Set up
        $problem1 = $this->createProblem(true, 33);
        $problem2 = $this->createProblem(true, 2);
        $problem3 = $this->createProblem(null, 0);
        $problem4 = $this->createProblem(false, 2);
        $problem5 = $this->createProblem(null, 9);

        $summary = $this->repo->attachChargeableServices($this->summary);
        $summary->save();

        //Run
        $list = $this->service->getBillablePatientsForMonth($this->practice->id, $summary->month_year);

        //Assert
        $this->assertMonthlySummary($this->summary, $problem1, $problem2, $list, $problem5);

        $freshSummary = $this->summary->fresh();
        $this->assertTrue($this->summary->chargeableServices()->where('code', 'CPT 99484')->exists());
        $this->assertTrue($this->summary->chargeableServices()->where('code', 'CPT 99490')->exists());
    }

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
        $this->assertMonthlySummary($this->summary, $problem1, $problem4, $list);
    }

    public function test_it_selects_g0511_code()
    {
        //Set up
        $g0511 = ChargeableService::whereCode('G0511')->firstOrFail();
        $this->practice->chargeableServices()->sync($g0511->pluck('id')->all());
        $problem1 = $this->createProblem(true, 33);
        $problem2 = $this->createProblem(true, 2);

        //act
        $summary = $this->repo->attachChargeableServices($this->summary);
        $summary->save();

        //assert
        $this->assertTrue($this->summary->chargeableServices()->where('code', 'G0511')->exists());
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

    public function test_it_stores_ccd_problem_with_cpm_id()
    {
        $admin = $this->createUser($this->practice->id, 'administrator');
        auth()->login($admin);

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

        $this->assertTrue(1 == $list->count());

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
     * Assert patient monthly summary.
     *
     * @param PatientMonthlySummary $summary
     * @param Problem               $ccmProblem1
     * @param Problem               $ccmProblem2
     * @param Collection            $list
     */
    private function assertMonthlySummary(
        PatientMonthlySummary $summary,
        Problem $ccmProblem1,
        Problem $ccmProblem2,
        Collection $list,
        Problem $bhiProblem = null
    ) {
        $summary     = $summary->fresh();
        $ccmProblem1 = $ccmProblem1->fresh();
        $ccmProblem2 = $ccmProblem2->fresh();
        $bhiProblem  = optional($bhiProblem)->fresh();

        if ($list->has('summaries')) {
            $summariesList = $list['summaries'];
            $this->assertEquals(1, $summariesList->getCollection()->count());
        } else {
            $summariesList = $list;
            $this->assertEquals(1, $summariesList->count());
        }

        $row = (new ApprovableBillablePatient($summariesList->first()))->resolve();

        $this->assertEquals($row['report_id'], $summary->id);
        $this->assertEquals($row['practice_id'], $this->practice->id);
        $this->assertEquals($row['practice'], $this->practice->display_name);
        $this->assertEquals($row['ccm'], round($summary->ccm_time / 60, 2));

        //CCM
        if ($summary->hasServiceCode('CPT 99490')) {
            $this->assertEquals($ccmProblem1->name, $row['problem1']);
            $this->assertEquals($ccmProblem1->icd10Code(), $row['problem1_code']);
            $this->assertEquals($ccmProblem2->name, $row['problem2']);
            $this->assertEquals($ccmProblem2->icd10Code(), $row['problem2_code']);

            $this->assertTrue((bool) $ccmProblem1->billable);
            $this->assertTrue((bool) $ccmProblem2->billable);
            $this->assertTrue((bool) $summary->approved, 'PatientSummary was not approved.');

            $this->assertTrue(2 == $this->patient->ccdProblems()->whereBillable(true)->count());
        }

        //BHI
        if ($summary->hasServiceCode('CPT 99484')) {
            $this->assertEquals(optional($bhiProblem)->name, $row['bhi_problem']);
            $this->assertEquals(optional($bhiProblem)->icd10Code(), $row['bhi_problem_code']);

            $this->assertTrue(Problem::find($summary->billableBhiProblems()->first()->id)->isBehavioral());
            $this->assertTrue((bool) $summary->approved, 'PatientSummary was not approved.');
        }
    }

    /**
     * Create a patient monthly summary.
     *
     * @param User   $patient
     * @param Carbon $monthYear
     * @param $ccmTime
     *
     * @return PatientMonthlySummary
     */
    private function createMonthlySummary(User $patient, Carbon $monthYear, $ccmTime)
    {
        return $patient->patientSummaries()->updateOrCreate([
            'month_year' => $monthYear->startOfMonth(),
        ], [
            'ccm_time'               => $ccmTime,
            'total_time'             => $ccmTime,
            'no_of_successful_calls' => 2,
        ]);
    }

    /**
     * Create a CCD\Problem.
     *
     * @param null $billable
     * @param null $cpmProblemId
     *
     * @return mixed
     */
    private function createProblem($billable = null, $cpmProblemId = null)
    {
        if ($cpmProblemId) {
            $cpmProblem = CpmProblem::find($cpmProblemId);
        }

        return $this->service->patientSummaryRepo->storeCcdProblem($this->patient, [
            'name' => isset($cpmProblem)
                ? $cpmProblem->name
                : $this->faker->name,
            'is_monitored'     => true,
            'billable'         => $billable,
            'cpm_problem_id'   => $cpmProblemId,
            'code'             => $this->faker->bankAccountNumber,
            'code_system_name' => 'ICD-10',
            'code_system_oid'  => '2.16.840.1.113883.6.3',
        ]);
    }
}
