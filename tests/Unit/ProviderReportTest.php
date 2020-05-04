<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Faker\Factory;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProviderReportTest extends TestCase
{
    protected $date;
    protected $faker;

    protected $user;

    protected function setUp(): void
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
            'remember_token'    => Str::random(10),
        ]);
        $this->assertNotNull($this->user);
    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_example()
    {
        $this->assertTrue(true);
    }
}
