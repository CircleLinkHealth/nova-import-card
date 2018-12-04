<?php

namespace Tests\Unit;

use App\Http\Resources\ApprovableBillablePatient;
use App\Models\CCD\Problem;
use App\PatientMonthlySummary;
use App\Practice;
use App\Services\ApproveBillablePatientsService;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Collection;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;

class PatientMonthlySummaryChargeableServicesTest extends TestCase
{
    use UserHelpers,
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
        $date     = Carbon::parse($this->monthYear)->format('M, Y');

        $response = $this->json('POST', route('monthly.billing.practice.services'), [
            'month_year'      => $this->monthYear,
            'practice_id'     => $practice->id,
            'default_code_id' => 1,
            'date'            => $date,
        ], ['X-Requested-With' => 'XMLHttpRequest']);

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
                'report_id'                   => $this->summary->id,
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
        return $patient->patientSummaries()->updateOrCreate(
            [
            'month_year' => $monthYear->startOfMonth(),
        ],
            ['ccm_time' => $ccmTime,]
        );
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

        $this->practice  = factory(Practice::class)->create();
        $this->patient   = $this->createUser($this->practice->id, 'participant');
        $this->service   = app(ApproveBillablePatientsService::class);
        $this->summary   = $this->createMonthlySummary($this->patient, Carbon::now(), 1400);
        $this->monthYear = Carbon::now()->startOfMonth()->toDateString();
    }
}
