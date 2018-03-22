<?php

namespace Tests\Unit;

use App\CLH\Faker\Patient\Problem;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FakerProblemTest extends TestCase
{
    protected $faker;

    /**
     *
     * @return void
     */
    public function test_it_returns_problem()
    {
        $problem = $this->faker->problem(false);

        $problemWithCodes = $this->faker->problem();

        $problemSet = $this->faker->problemSet();

        $this->assertTrue(true);
    }

    /**
     *
     */
    protected function setUp()
    {
        parent::setUp();

        $this->faker = new Problem();
    }
}
