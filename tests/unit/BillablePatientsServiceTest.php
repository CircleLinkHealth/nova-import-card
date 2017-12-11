<?php

namespace Tests\Unit;

use App\Practice;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BillablePatientsServiceTest extends TestCase
{
    use //DatabaseTransactions,
        UserHelpers;

    private $practice;
    private $patient;

    protected function setUp()
    {
        parent::setUp();

        $this->practice = factory(Practice::class)->create();
        $this->patient = $this->createUser($this->practice->id, 'participant');

        $problem1 = $this->patient->ccdProblems()->create([
            'name' => 'Problem 1',
            'billable' => true,
            'cpm_problem_id' => 33,
        ]);

        $problem2 = $this->patient->ccdProblems()->create([
            'name' => 'Problem 2',
            'billable' => true,
            'cpm_problem_id' => 2,
        ]);

        $summary = $this->patient->patientSummaries()->create([
            'month_year' => Carbon::now()->startOfMonth()->toDateString(),
            'ccm_time' => 1400,
            'problem_1' => $problem1,
            'problem_2' => $problem2,
        ]);
    }

    public function test_ () {

    }
}
