<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Traits\Tests\CareAmbassadorHelpers;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
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
        //make sure those enrollees have been 'called'

        //create some page timers for ca without enrollee IDs, keep ids

        //run command

        //for each of page timers assert that they have been assigned to an enrollee belonging to a CA

        //optional: use new relationship to make sure they are withing coverage
    }

    public function test_practice_and_ca_enrollment_kpis_match()
    {
        //create multiple CAs with multiple enrollees assigned
        //foreach ca, foreach enrollee, perform action and add page timers (page timer add to helper class)

        //get total of CA KPIs, compare against practice KPIs
    }
}
