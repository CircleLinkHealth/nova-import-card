<?php

namespace Tests\Unit;

use CircleLinkHealth\Customer\Entities\User;
use Carbon\Carbon;
use Faker\Factory;
use Tests\TestCase;

class ProviderReportTest extends TestCase
{
    protected $faker;

    protected $user;

    protected $date;
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }

    public function setUp()
    {
        parent::setUp();

        $this->faker = $faker = Factory::create();

        $this->date = Carbon::now();

        $this->user = User::create([
            'first_name'        => $this->faker->name,
            'last_name'         => $this->faker->lastName,
            'display_name'      => $this->faker->name,
            'email'             => $this->faker->unique()->safeEmail,
            'email_verified_at' => $this->date,
            'password'          => bcrypt('secret'),
            'remember_token'    => str_random(10),
        ]);
        $this->assertNotNull($this->user);


    }
}
