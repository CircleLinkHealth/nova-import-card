<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\CLH\Faker\Patient\Problem;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\UserHelpers;

class FakerProblemTest
{
    use
        DatabaseTransactions;
    use UserHelpers;

    protected $faker;
    protected $patient;
    protected $practice;

//    /**
//     *
//     * @return void
//     */
//    public function test_it_returns_problem()
//    {
//        $problem = $this->faker->problem(false);
//
//        $this->assertInstanceOf(
//            'App\Models\CCD\Problem', $problem
//        );
//
//        $problemWithName = $this->faker->problem(false, 'Hypertension');
//
//        $this->assertInstanceOf(
//            'App\Models\CCD\Problem', $problemWithName
//        );
//
//        $problemWithCodes = $this->faker->problem();
//
//        $problemSet = $this->faker->problemSet();
//
//        $attachProblemSet = $this->faker->attachProblemSet($this->patient);
//
//        $this->assertInstanceOf('CircleLinkHealth\Customer\Entities\User', $attachProblemSet);
//
//
//    }

    protected function setUp()
    {
        parent::setUp();

        $this->practice = factory(Practice::class)->create();
        $this->faker    = new Problem();
        $this->patient  = $this->createUser($this->practice->id, 'participant');
    }
}
