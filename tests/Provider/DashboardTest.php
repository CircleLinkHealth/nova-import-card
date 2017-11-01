<?php
namespace Tests\Provider;

use Tests\TestCase;
use App\Role;
use Faker\Factory;

class DashboardTest extends TestCase
{
    protected $faker;

    /**
     * @var App\User $programLead
     */
    protected $programLead;

    public function __construct()
    {
        parent::__construct();

        $this->faker = Factory::create();
    }

    public function testMain()
    {
        $this->createPractice();
        $this->inviteStaff();
    }

    public function createPractice()
    {
        $role = Role::whereName('practice-lead')->first();

        $this->programLead = factory(App\User::class)
            ->create();

        $this->programLead->roles()->attach($role->id);

        $name = $this->faker->company;
        $description = $this->faker->text();

        $this->actingAs($this->programLead)
            ->visit(route('get.create.practice'))
            ->type($name, 'name')
            ->type($description, 'description')
            ->press('update-practice');

        $this->assertDatabaseHas('wp_blogs', [
            'name'         => str_slug($name),
            'display_name' => $name,
            'description'  => $description,
            'user_id'      => auth()->user()->ID,
        ]);
    }

    public function inviteStaff()
    {
        $inviteeEmail = $this->faker->email;

        $role = Role::whereName('practice-lead')->first();

        $subject = 'You are invited to join CPM';
        $message = 'Please create a CPM account.';

        $this->actingAs($this->programLead)
            ->visit(route('get.create.staff'))
            ->type($inviteeEmail, 'email')
            ->type($subject, 'subject')
            ->type($message, 'message')
            ->type($role->id, 'role')
            ->press('Invite');

        $this->assertDatabaseHas('invites', [
            'inviter_id' => $this->programLead->ID,
            'role_id'    => $role->id,
            'email'      => $inviteeEmail,
            'subject'    => $subject,
            'message'    => $message,
        ]);
    }

    public function testDashboardGet()
    {
        $response = $this->get(route('get.provider.dashboard'));
    }
}
