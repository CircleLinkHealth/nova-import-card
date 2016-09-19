<?php

use App\Role;
use Faker\Factory;

class PracticeTest extends TestCase
{
    /**
     * @var App\User $programLead
     */
    protected $programLead;

    public function testCreatePractice()
    {
        $faker = Factory::create();

        $role = Role::whereName('program-lead')->first();

        $user = factory(App\User::class)
            ->create();

        $user->roles()->attach($role->id);

        $name = $faker->domainName;
        $description = $faker->text();

        $this->actingAs($user)
            ->type('name', $name)
            ->type('description', $description)
            ->type('url', $name)
            ->press('Update Practice');
    }
}
