<?php

namespace Tests\Unit;

use App\Practice;
use App\Services\ApproveBillablePatientsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;
use Carbon\Carbon;
use App\Http\Resources\ApprovableBillablePatient;
use App\Models\CCD\Problem;
use App\PatientMonthlySummary;
use App\User;
use Illuminate\Support\Collection;



class PatientMonthlySummaryChargeableServices extends TestCase
{
    use DatabaseTransactions,
        UserHelpers,
        WithoutMiddleware,
        WithFaker;

    private $practice;
    private $patient;
    private $service;
    private $summary;
    private $monthYear;


    /**
     *
     */
    public function test_it_updates_practice_default_service()
    {
        $practice = $this->practice;

        $response = $this->json('POST', route('monthly.billing.practice.services'), [
            'month_year'      => $this->monthYear,
            'practice_id'     => $practice->id,
            'default_code_id' => 1,
        ],['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(200);
    }

    /**
     *
     *
     */
    public function test_it_updates_summary_chargeable_services()
    {
        $practice = $this->practice;
        $patient  = $this->patient;
        $services = [1, 2];

        $response = $this
            ->json('POST', route('monthly.billing.summary.services'), [
                'month_year'                  => $this->monthYear,
                'practice_id'                 => $practice->id,
                'patient_id'                  => $patient->id,
                'patient_chargeable_services' => $services,
            ], ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(200);


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
    protected function setUp()
    {
        parent::setUp();

        $this->practice = factory(Practice::class)->create();
        $this->patient  = $this->createUser($this->practice->id, 'participant');
        $this->service  = app(ApproveBillablePatientsService::class);
        $this->summary  = $this->createMonthlySummary($this->patient, Carbon::now(), 1400);
        $this->monthYear = Carbon::now()->startOfMonth()->toDateString();
    }
}
