<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Services\Enrollment\CareAmbassadorKPIs;
use App\Services\Enrollment\PracticeKPIs;
use App\Traits\Tests\CareAmbassadorHelpers;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class EnrollmentKPIsTest extends TestCase
{
    use \App\Traits\Tests\UserHelpers;
    use CareAmbassadorHelpers;

    protected $careAmbassadorUser;
    protected $enrollee;
    protected $practice;
    protected $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->practice           = factory(Practice::class)->create();
        $this->careAmbassadorUser = $this->createUser($this->practice->id, 'care-ambassador');
        $this->provider           = $this->createUser($this->practice->id, 'provider');
    }

    public function test_command_adds_ca_unassigned_time_to_ca_enrollees()
    {
        //assign some enrollees to CA
        auth()->loginUsingId($this->careAmbassadorUser->id);
        $enrollees   = $this->createAndAssignEnrolleesToCA($this->practice, $this->careAmbassadorUser, 5);
        $enrolleeIds = $enrollees->pluck('id')->toArray();

        //make sure those enrollees have been 'called'
        foreach ($enrollees as $enrollee) {
            $this->performActionOnEnrollee($enrollee);
        }

        //create some page timers for ca without enrollee IDs, keep ids
        $pageTimers   = $this->createPageTimersForCA($this->careAmbassadorUser, [], 5);
        $pageTimerIds = $pageTimers->pluck('id')->toArray();

        //run command
        Artisan::call('ca:unassignedTimeToEnrollees');

        //for each of page timers assert that they have been assigned to an enrollee belonging to a CA
        foreach ($this->careAmbassadorUser->pageTimersAsProvider()->get() as $pageTimer) {
            $this->assertTrue(in_array($pageTimer->id, $pageTimerIds));
            $this->assertNotNull($pageTimer->enrollee_id);
            $this->assertTrue(in_array($pageTimer->enrollee_id, $enrolleeIds));
        }
    }

    public function test_practice_and_ca_enrollment_kpis_match()
    {
        $practice = factory(Practice::class)->create();

        //create multiple CAs with multiple enrollees assigned
        $cas = [];

        for ($i = 5; $i > 0; --$i) {
            $ca                              = $this->createUser($practice->id, 'care-ambassador');
            $ca->careAmbassador->hourly_rate = floatval('20.5');
            $ca->careAmbassador->save();
            $cas[] = $ca;
        }

        $caActionTypes = collect([
            Enrollee::UNREACHABLE,
            Enrollee::SOFT_REJECTED,
            Enrollee::REJECTED,
            Enrollee::CONSENTED,
        ]);

        $caKPIs = [];

        //foreach ca, foreach enrollee, perform action and add page timers
        foreach ($cas as $ca) {
            auth()->login($ca);
            $enrollees = $this->createAndAssignEnrolleesToCA($practice, $ca, 5);
            $this->createPageTimersForCA($ca, $enrollees->pluck('id')->toArray(), 5);

            foreach ($enrollees as $enrollee) {
                $this->performActionOnEnrollee($enrollee, $caActionTypes->random());
            }

            $caKPIs[] = CareAmbassadorKPIs::get($ca, Carbon::now()->startOfMonth(), Carbon::now());
        }

        //get total of CA KPIs, compare against practice KPIs

        $practiceKPIs = PracticeKPIs::get($practice, Carbon::now()->startOfMonth()->toDateString(), Carbon::now()->toDateString());

        $caKPIs               = collect($caKPIs);
        $caUniqueCalls        = $caKPIs->sum('total_calls');
        $caTotalTimeInSeconds = $caKPIs->sum('total_seconds');
        $caCost               = $caKPIs->sum('total_cost');

        $this->assertEquals($caUniqueCalls, $practiceKPIs['unique_patients_called']);
        $this->assertEquals($caTotalTimeInSeconds, $practiceKPIs['total_time_seconds']);
        $this->assertEquals($caCost, $practiceKPIs['total_cost']);
    }
}
