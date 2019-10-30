<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Http\Resources\ApprovableBillablePatient;
use App\Models\CCD\Problem;
use App\Services\ApproveBillablePatientsService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Collection;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;

class PatientMonthlySummaryChargeableServicesTest extends TestCase
{
    use UserHelpers;
    use
        WithFaker;
    use
        WithoutMiddleware;
    private $monthYear;
    private $patient;

    private $practice;
    private $service;
    private $summary;

    protected function setUp()
    {
        parent::setUp();

        $this->practice  = factory(Practice::class)->create();
        $this->patient   = $this->createUser($this->practice->id, 'participant');
        $this->service   = app(ApproveBillablePatientsService::class);
        $this->summary   = $this->createMonthlySummary($this->patient, Carbon::now(), 1400);
        $this->monthYear = Carbon::now()->startOfMonth()->toDateString();
    }

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
     * Assert patient monthly summary.
     *
     * @param \CircleLinkHealth\Customer\Entities\PatientMonthlySummary $summary
     * @param Problem                                                   $problem1
     * @param Problem                                                   $problem2
     * @param Collection                                                $list
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

        $this->assertTrue(1 == $list->count());

        $row = (new ApprovableBillablePatient($list->first()))->resolve();

        $this->assertEquals($row['report_id'], $summary->id);
        $this->assertEquals($row['practice'], $this->practice->display_name);
        $this->assertEquals($row['ccm'], round($summary->ccm_time / 60, 2));
        $this->assertEquals($row['problem1'], $problem1->name);
        $this->assertEquals($row['problem1_code'], $problem1->icd10Code());
        $this->assertEquals($row['problem2'], $problem2->name);
        $this->assertEquals($row['problem2_code'], $problem2->icd10Code());

        $this->assertTrue((bool) $problem1->billable);
        $this->assertTrue((bool) $problem2->billable);

        $this->assertTrue(2 == $this->patient->ccdProblems()->whereBillable(true)->count());
    }

    /**
     * Create a patient monthly summary.
     *
     * @param User   $patient
     * @param Carbon $monthYear
     * @param $ccmTime
     *
     * @return \CircleLinkHealth\Customer\Entities\PatientMonthlySummary
     */
    private function createMonthlySummary(User $patient, Carbon $monthYear, $ccmTime)
    {
        return $patient->patientSummaries()->updateOrCreate(
            [
                'month_year' => $monthYear->startOfMonth(),
            ],
            ['ccm_time' => $ccmTime]
        );
    }
}
