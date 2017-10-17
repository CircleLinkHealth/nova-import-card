<?php

namespace Tests\Unit;

use App\Models\CCD\Problem;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProblemCodesTest extends TestCase
{
    use DatabaseTransactions;

    private $snomedProblem;
    private $icd10Problem;
    private $icd9Problem;

    protected function setUp()
    {
        parent::setUp();

        $patient = User::find(874);

        $this->icd9Problem = $this->makeIcd9Problem($patient);
        $this->icd10Problem = $this->makeIcd10Problem($patient);
        $this->snomedProblem = $this->makeSnomedProblem($patient);
    }

    public function test_is_icd_9() {
        $this->assertTrue($this->icd9Problem->isIcd9());
    }

    public function test_is_icd_10() {
        $this->assertTrue($this->icd10Problem->isIcd10());
    }

    public function test_is_snomed() {
        $this->assertTrue($this->snomedProblem->isSnomed());
    }

    public function test_icd10_code() {
        $this->assertNotNull($this->icd9Problem->icd10Code());
        $this->assertNotNull($this->icd10Problem->icd10Code());
        $this->assertNull($this->snomedProblem->icd10Code());
    }

    private function makeIcd9Problem($patient)
    {
        return Problem::create([
            'problem_import_id'  => null,
            'ccda_id'            => null,
            'patient_id'         => $patient->id,
            'vendor_id'          => 1,
            'ccd_problem_log_id' => null,
            'name'               => 'Test Icd 9 Problem',
            'code'               => "401.1",
            'code_system'        => "2.16.840.1.113883.6.103",
            'code_system_name'   => "ICD-9",
            'activate'           => 1,
            'cpm_problem_id'     => 2,
        ]);
    }

    private function makeIcd10Problem($patient)
    {
        return Problem::create([
            'problem_import_id'  => null,
            'ccda_id'            => null,
            'patient_id'         => $patient->id,
            'vendor_id'          => 1,
            'ccd_problem_log_id' => null,
            'name'               => 'Test Icd 10 Problem',
            'code'               => "N18.3",
            'code_system'        => "2.16.840.1.113883.6.3",
            'code_system_name'   => "ICD-10",
            'activate'           => 1,
            'cpm_problem_id'     => 8,
        ]);
    }

    private function makeSnomedProblem($patient)
    {
        return Problem::create([
            'problem_import_id'  => null,
            'ccda_id'            => null,
            'patient_id'         => $patient->id,
            'vendor_id'          => 1,
            'ccd_problem_log_id' => null,
            'name'               => 'Test Snomed Problem',
            'code'               => "313436004",
            'code_system'        => "2.16.840.1.113883.6.96",
            'code_system_name'   => "SNOMED CT",
            'activate'           => 1,
            'cpm_problem_id'     => 1,
        ]);
    }
}
