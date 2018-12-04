<?php

namespace Tests\Unit;

use App\CLH\Faker\Patient\Problem;
use App\Practice;
use App\Models\CCD\Problem as CcdProblem;
use Tests\TestCase;
use Tests\Helpers\UserHelpers;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Collection;

class FakerProblemTest
{
    use UserHelpers,
        DatabaseTransactions;


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
//        $this->assertInstanceOf('App\User', $attachProblemSet);
//
//
//    }

    /**
     *
     */
    protected function setUp()
    {
        parent::setUp();

        $this->practice = factory(Practice::class)->create();
        $this->faker = new Problem();
        $this->patient = $this->createUser($this->practice->id, 'participant');
    }
}
