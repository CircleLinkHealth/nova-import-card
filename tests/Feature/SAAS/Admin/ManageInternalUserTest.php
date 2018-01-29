<?php

namespace Tests\Feature\SAAS\Admin;

use App\Practice;
use App\Role;
use App\User;
use Tests\DuskTestCase;
use Tests\Helpers\UserHelpers;

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
    public function test_form_creates_internal_user()
    {
        $practice      = factory(Practice::class)->create([]);
        $saasAdminRole = Role::whereName('saas-admin')->first();

        $adminUser       = $this->createUser($practice->id, 'saas-admin');
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
                    ->press('.submit')
                    ->pause(2000)
                    ->assertPathBeginsWith('/saas/admin/users/');
        });

        $createdUser = User::whereEmail($newInternalUser->email)->first();

        $this->assertDatabaseHas('users', [
            'username'   => $newInternalUser->username,
            'email'      => $newInternalUser->email,
            'first_name' => $newInternalUser->first_name,
            'last_name'  => $newInternalUser->last_name,
        ]);

        $this->assertDatabaseHas('practice_role_user', [
            'program_id' => $practice->id,
            'role_id'     => $saasAdminRole->id,
            'user_id'     => $createdUser->id,
        ]);
    }
}
