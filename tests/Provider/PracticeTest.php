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

        $this->programLead = factory(App\User::class)
            ->create();

        $this->programLead->roles()->attach($role->id);

        $name = $faker->domainName;
        $description = $faker->text();

        $this->actingAs($this->programLead)
            ->visit(route('get.create.practice'))
            ->type($name, 'name')
            ->type($description, 'description')
            ->type($name, 'url')
            ->press('Update Practice');

        $this->seeInDatabase('wp_blogs', [
            'name' => $name,
            'display_name' => $name,
            'description' => $description,
            'user_id' => auth()->user()->ID,
        ]);
    }
}
