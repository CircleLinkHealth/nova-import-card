<?php

namespace Tests\Unit;

use App\PatientMonthlySummary;
use App\Practice;
use App\Services\ApproveBillablePatientsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;

class BillablePatientsServiceTest extends TestCase
{
    use DatabaseTransactions,
        UserHelpers;

    private $practice;
    private $patient;
    private $service;

    public function test_it_selects_billable_ccd_problem_without_cpm_problem_id()
    {
        $problem1Code = 'icd 10 1';

        $problem1 = $this->service->storeCcdProblem($this->patient, [
            'name'             => 'Problem 1',
            'billable'         => true,
            'cpm_problem_id'   => 33,
            'code'             => $problem1Code,
            'code_system_name' => 'ICD-10',
            'code_system_oid'  => '2.16.840.1.113883.6.3',
        ]);


        $problem2Code = 'icd 10 2';
        $problem2     = $this->service->storeCcdProblem($this->patient, [
            'name'             => 'Problem 2',
            'billable'         => true,
            'cpm_problem_id'   => null,
            'code'             => $problem2Code,
            'code_system_name' => 'ICD-10',
            'code_system_oid'  => '2.16.840.1.113883.6.3',
        ]);

        $problem3     = $this->service->storeCcdProblem($this->patient, [
            'name'             => 'Problem 3',
            'billable'         => false,
            'cpm_problem_id'   => 0,
            'code'             => 'prob3',
            'code_system_name' => 'ICD-10',
            'code_system_oid'  => '2.16.840.1.113883.6.3',
        ]);

        $problem4     = $this->service->storeCcdProblem($this->patient, [
            'name'             => 'Problem 4',
            'billable'         => false,
            'cpm_problem_id'   => 2,
            'code'             => 'prob4',
            'code_system_name' => 'ICD-10',
            'code_system_oid'  => '2.16.840.1.113883.6.3',
        ]);

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

    protected function setUp()
    {
        parent::setUp();

        $this->practice = factory(Practice::class)->create();
        $this->patient  = $this->createUser($this->practice->id, 'participant');
        $this->service  = app(ApproveBillablePatientsService::class);
    }
}
