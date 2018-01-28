<?php

namespace Tests\Feature\SAAS\Admin;

use App\Practice;
use App\Role;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DuskTestCase;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ManageInternalUserTest extends DuskTestCase
{
    use //DatabaseTransactions,
        UserHelpers;

    /**
     * A basic test example.
     *
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function test_form_displays_correctly()
    {
        $practice = factory(Practice::class)->create([]);
        $saasAdminRole = Role::whereName('saas-admin')->first();

        $adminUser = $this->createUser($practice->id, 'saas-admin');
        $newInternalUser = factory(User::class)->make([]);

        $this->browse(function ($browser) use ($adminUser, $newInternalUser, $saasAdminRole, $practice) {
            $browser->loginAs($adminUser)
                    ->visit(route('saas-admin.users.create'))
                    ->assertRouteIs('saas-admin.users.create')
                    ->type('user[username]', $newInternalUser->username)
                    ->type('user[email]', $newInternalUser->email)
                    ->type('user[first_name]', $newInternalUser->first_name)
                    ->type('user[last_name]', $newInternalUser->last_name)
                    ->select('role', $saasAdminRole->id)
                    ->select('practices[]', $practice->id)
                    ->press('Create User');
        });

        $this->assertDatabaseHas('users', [
            'username' => $newInternalUser->username,
            'email' => $newInternalUser->email,
            'first_name' => $newInternalUser->first_name,
            'last_name' => $newInternalUser->last_name,
        ]);
    }
}
