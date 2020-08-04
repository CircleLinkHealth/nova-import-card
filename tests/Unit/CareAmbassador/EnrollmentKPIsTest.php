<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Traits\Tests\CareAmbassadorHelpers;
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

        //optional: use new relationship to make sure they are withing coverage
    }

    public function test_practice_and_ca_enrollment_kpis_match()
    {
        //create multiple CAs with multiple enrollees assigned
        //foreach ca, foreach enrollee, perform action and add page timers (page timer add to helper class)

        //get total of CA KPIs, compare against practice KPIs
    }
}
