<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature\SAAS\Admin;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use Tests\DuskTestCase;

class ManageInternalUserTest extends DuskTestCase
{
//    use \App\Traits\Tests\UserHelpers;
//    private $adminUser;
//    private $practice;
//
//    private $saasAdminRole;
//
//    protected function setUp():void
//    {
//        parent::setUp();
//
//        $this->practice      = factory(Practice::class)->create([]);
//        $this->saasAdminRole = Role::whereName('saas-admin')->first();
//        $this->adminUser     = $this->createUser($this->practice->id, 'saas-admin');
//    }
//
//    public function test_403_if_not_saas_admin()
//    {
//        $practice     = $this->practice;
//        $loggedInUser = $this->createUser($practice->id, 'provider');
//
//        $this->browse(function ($browser) use ($loggedInUser, $practice) {
//            $browser->loginAs($loggedInUser)
//                ->visit(route('saas-admin.users.create'))
//                ->assertDontSee('Create User');
//        });
//    }
//
//    /**
//     * @throws \Exception
//     * @throws \Throwable
//     */
//    public function test_form_creates_internal_user()
//    {
//        $practice        = $this->practice;
//        $saasAdminRole   = $this->saasAdminRole;
//        $loggedInUser    = $this->adminUser;
//        $newInternalUser = factory(User::class)->make([]);
//
//        $this->browse(function ($browser) use ($loggedInUser, $newInternalUser, $saasAdminRole, $practice) {
//            $browser->loginAs($loggedInUser)
//                ->visit(route('saas-admin.users.create'))
//                ->assertRouteIs('saas-admin.users.create')
//                ->type('user[username]', $newInternalUser->username)
//                ->type('user[email]', $newInternalUser->email)
//                ->type('user[first_name]', $newInternalUser->first_name)
//                ->type('user[last_name]', $newInternalUser->last_name)
//                ->select('role', $saasAdminRole->id)
//                ->select('practices[]', $practice->id)
//                ->press('.submit')
//                ->pause(2000)
//                ->assertPathBeginsWith('/saas/admin/users/');
//        });
//
//        $createdUser = User::whereEmail($newInternalUser->email)->first();
//
//        $this->assertDatabaseHas('users', [
//            'username'   => $newInternalUser->username,
//            'email'      => $newInternalUser->email,
//            'first_name' => $newInternalUser->first_name,
//            'last_name'  => $newInternalUser->last_name,
//        ]);
//
//        $this->assertDatabaseHas('practice_role_user', [
//            'program_id' => $practice->id,
//            'role_id'    => $saasAdminRole->id,
//            'user_id'    => $createdUser->id,
//        ]);
//    }
//
//    /**
//     * @throws \Exception
//     * @throws \Throwable
//     */
//    public function test_form_shows_validation_errors()
//    {
//        $practice        = $this->practice;
//        $saasAdminRole   = $this->saasAdminRole;
//        $loggedInUser    = $this->adminUser;
//        $newInternalUser = factory(User::class)->create([]);
//
//        $this->browse(function ($browser) use ($loggedInUser, $newInternalUser, $saasAdminRole, $practice) {
//            $browser->loginAs($loggedInUser)
//                ->visit(route('saas-admin.users.create'))
//                ->assertRouteIs('saas-admin.users.create')
//                ->type('user[username]', $newInternalUser->username)
//                ->type('user[email]', $newInternalUser->email)
//                ->type('user[first_name]', $newInternalUser->first_name)
//                ->type('user[last_name]', $newInternalUser->last_name)
//                ->select('role', $saasAdminRole->id)
//                ->select('practices[]', $practice->id)
//                ->press('.submit')
//                ->pause(2000)
//                ->assertSee('The user.email has already been taken.')
//                ->assertSee('The user.username has already been taken.')
//                ->assertRouteIs('saas-admin.users.create');
//        });
//    }
}
