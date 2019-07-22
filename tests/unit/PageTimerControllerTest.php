<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Models\CPM\CpmProblem;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\Role;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Collection;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;

class PageTimerControllerTest extends TestCase
{
    use UserHelpers;
    use
        WithFaker;
    use
        WithoutMiddleware;
    const BHI = 'bhi';
    const CCM = 'ccm';
    const MIX = 'mix';

    //20 mins in seconds
    const TWENTY_MINUTES = 1200;

    private $patient;

    /**
     * @var \CircleLinkHealth\Customer\Entities\Practice
     */
    private $practice;
    private $provider;

    protected function setUp()
    {
        parent::setUp();
        $this->practice = factory(Practice::class)->create();
        $this->provider = $this->createUser($this->practice->id, 'care-center');
        $this->patient  = $this->createUser($this->practice->id, 'participant');

        //fulfill conditions for patient to be BHI
        Patient::whereUserId($this->patient->id)
            ->update(
                [
                    'ccm_status'   => Patient::ENROLLED,
                    'consent_date' => Carbon::now(),
                ]
               );

        $defaultServices = ChargeableService::defaultServices();
        $this->practice->chargeableServices()->sync($defaultServices->pluck('id')->all());

        $cpmProblem = CpmProblem::whereIsBehavioral(true)->firstOrFail();

        $this->patient
            ->ccdProblems()
            ->create(
                [
                    'name'             => $cpmProblem->name,
                    'is_monitored'     => true,
                    'billable'         => true,
                    'cpm_problem_id'   => $cpmProblem->id,
                    'code'             => '12345',
                    'code_system_name' => 'ICD-10',
                    'code_system_oid'  => '2.16.840.1.113883.6.3',
                ]
            );

        //add provider role
        $role = Role::where('name', 'provider')->first();
        $this->provider->roles()->attach($role->id);
    }

    public function test_bhi_time_is_stored()
    {
        $this->runTestFor(self::BHI);
    }

    public function test_ccm_time_is_stored()
    {
        $this->runTestFor(self::CCM);
    }

    public function test_mixed_activities()
    {
        $this->runTestFor(self::MIX);
    }

    /**
     * @param Collection $activities
     * @param Carbon     $date
     */
    private function dbAssertionsForCcm(Collection $activities, Carbon $date)
    {
        $nurseInfo = $this->provider->nurseInfo;

        $sum = $activities->sum('duration');

        $ccmTotal = (int) $activities->sum(
            function ($act) {
                if ( ! (bool) $act['is_behavioral']) {
                    return (int) $act['duration'];
                }
            }
        );

        $bhiTotal = (int) $activities->sum(
            function ($act) {
                if ((bool) $act['is_behavioral']) {
                    return (int) $act['duration'];
                }
            }
        );

        $this->assertEquals($sum, $bhiTotal + $ccmTotal);

        $sumAfterTarget   = 0;
        $sumTowardsTarget = 0;

        foreach ([$bhiTotal, $ccmTotal] as $typeTotal) {
            if ($typeTotal >= self::TWENTY_MINUTES) {
                $sumTowardsTarget += self::TWENTY_MINUTES;
                $sumAfterTarget   += $typeTotal - self::TWENTY_MINUTES;
            } else {
                $sumTowardsTarget += $typeTotal;
            }
        }

        $this->assertInstanceOf(Nurse::class, $nurseInfo);

        foreach ($activities as $act) {
            $this->assertDatabaseHas(
                'lv_page_timer',
                [
                    'patient_id'    => $this->patient->id,
                    'provider_id'   => $this->provider->id,
                    'duration'      => $act['duration'],
                    'activity_type' => $act['name'],
                    'title'         => $act['title'],
                    'url_full'      => $act['url'],
                    'url_short'     => $act['url_short'],
                ]
            );

            $this->assertDatabaseHas(
                'lv_activities',
                [
                    'patient_id'    => $this->patient->id,
                    'provider_id'   => $this->provider->id,
                    'duration'      => $act['duration'],
                    'type'          => $act['name'],
                    'is_behavioral' => $act['is_behavioral'],
                ]
            );
        }

        $this->assertDatabaseHas(
            'patient_monthly_summaries',
            [
                'patient_id' => $this->patient->id,
                'month_year' => $date->copy()->startOfMonth(),
                'total_time' => $sum,
                'ccm_time'   => $ccmTotal,
                'bhi_time'   => $bhiTotal,
            ]
        );

        $this->assertDatabaseHas(
            'nurse_monthly_summaries',
            [
                'nurse_id'               => $nurseInfo->id,
                'month_year'             => $date->copy()->startOfMonth(),
                'accrued_after_ccm'      => $sumAfterTarget,
                'accrued_towards_ccm'    => $sumTowardsTarget,
                'no_of_calls'            => 0,
                'no_of_successful_calls' => 0,
            ]
        );
    }

    private function fakeActivity(Carbon $date, $isBehavioral = false)
    {
        return [
            'start_time'    => $date->toDateTimeString(),
            'duration'      => $this->faker()->numberBetween(1, 500),
            'url'           => $this->faker()->url,
            'url_short'     => $this->faker()->url,
            'name'          => $this->faker()->realText(30),
            'title'         => $this->faker()->text(10),
            'is_behavioral' => $isBehavioral,
        ];
    }

    private function getSampleActivities(Carbon $date, $type = self::MIX)
    {
        $numberOfActivities = $this->faker()->numberBetween(1, 30);

        $activities = collect();

        while (0 !== $numberOfActivities) {
            if (self::CCM == $type) {
                $isBehavioral = false;
            } elseif (self::BHI == $type) {
                $isBehavioral = true;
            } else {
                $isBehavioral = $this->faker()->boolean;
            }

            $activities->push($this->fakeActivity($date, $isBehavioral));
            --$numberOfActivities;
        }

        return $activities;
    }

    private function runTestFor($type = self::MIX)
    {
        $date = Carbon::now();

        $activities = $this->getSampleActivities($date, $type);

        $response = $this->submit($activities);

        $response->assertStatus(201);

        $this->dbAssertionsForCcm($activities, $date);
    }

    private function submit(Collection $activities)
    {
        return $this->json(
            'POST',
            route('api.pagetracking'),
            [
                'patientId'  => $this->patient->id,
                'providerId' => $this->provider->id,
                'programId'  => '',
                'ipAddr'     => '',
                'submitUrl'  => 'url',
                'activities' => $activities->all(),
            ]
        );
    }
}
