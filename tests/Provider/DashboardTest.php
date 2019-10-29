<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Provider;

use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use Faker\Factory;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DashboardTest extends DuskTestCase
{
    protected $faker;

    /**
     * @var User
     */
    protected $programLead;
    private $practiceSlug;

    public function __construct()
    {
        parent::__construct();

        $this->faker = Factory::create();
    }

    public function createPractice()
    {
        $role = Role::whereName('practice-lead')->first();

        $this->programLead = factory(User::class)
            ->create();

        $this->programLead->roles()->attach($role->id);

        $name = $this->faker->company;

        $this->browse(function (Browser $browser) use ($name) {
            $browser->loginAs($this->programLead)
                ->visitRoute('get.onboarding.create.practice', ['lead_id' => $this->programLead->id])
                ->assertRouteIs('get.onboarding.create.practice', ['lead_id' => $this->programLead->id])
                ->type('name', $name)
                ->press('@save-practice');
        });

        $this->practiceSlug = Str::slug($name);

        $this->assertDatabaseHas('practices', [
            'name'         => $this->practiceSlug,
            'display_name' => $name,
            'user_id'      => $this->programLead->id,
        ]);
    }

    public function inviteStaff()
    {
        $inviteeEmail = $this->faker->email;

        $role = Role::whereName('practice-lead')->first();

        $subject = 'You are invited to join CPM';
        $message = 'Please create a CPM account.';

        $this->browse(function (Browser $browser) use ($inviteeEmail, $subject, $message, $role) {
            $browser
                ->visitRoute('get.onboarding.create.staff', ['practiceSlug' => $this->practiceSlug])
                ->assertRouteIs('get.onboarding.create.staff', ['practiceSlug' => $this->practiceSlug])
                ->type('email', $inviteeEmail)
                ->type('subject', $subject)
                ->type('message', $message)
                ->type('role', $role->id)
                ->press('Invite');
        });

        $this->assertDatabaseHas('invites', [
            'inviter_id' => $this->programLead->ID,
            'role_id'    => $role->id,
            'email'      => $inviteeEmail,
            'subject'    => $subject,
            'message'    => $message,
        ]);
    }

    public function test_main()
    {
        $this->createPractice();
        $this->inviteStaff();
    }
}
