<?php

namespace Tests\Unit;

use App\Practice;
use App\Services\ApproveBillablePatientsService;
use Carbon\Carbon;
use Faker\Generator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
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

    public function test_it_selects_billable_ccd_problem_without_cpm_problem_id()
    {
        $problem1 = $this->createProblem(true, 33);
        $problem2 = $this->createProblem();
        $problem3 = $this->createProblem(false, 0);
        $problem4 = $this->createProblem(false, 2);

        $summary = $this->patient->patientSummaries()->create([
            'month_year' => Carbon::now()->startOfMonth()->toDateString(),
            'ccm_time'   => 1400,
            'problem_1'  => $problem1->id,
        ]);

        $list = $this->service->patientsToApprove($this->practice->id, Carbon::now());

        $this->assertTrue($list->count() == 1);

        $first = $list->first();

        $this->assertEquals($first['report_id'], $summary->id);
        $this->assertEquals($first['practice'], $this->practice->display_name);
        $this->assertEquals($first['ccm'], round($summary->ccm_time / 60, 2));
        $this->assertEquals($first['problem1'], $problem1->name);
        $this->assertEquals($first['problem1_code'], $problem1->icd10Code());
        $this->assertEquals($first['problem2'], $problem2->name);
        $this->assertEquals($first['problem2_code'], $problem2->icd10Code());
    }

    public function createProblem($billable = true, $cpmProblemId = null)
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

    protected function setUp()
    {
        parent::setUp();

        $this->practice = factory(Practice::class)->create();
        $this->patient  = $this->createUser($this->practice->id, 'participant');
        $this->service  = app(ApproveBillablePatientsService::class);
    }
}
