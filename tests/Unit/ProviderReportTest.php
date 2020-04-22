<?php

namespace Tests\Unit;

use Illuminate\Support\Str;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
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
            'remember_token'    => Str::random(10),
        ]);
        $this->assertNotNull($this->user);
    }
}
