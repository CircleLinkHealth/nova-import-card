<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use CircleLinkHealth\Customer\Entities\Permission;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;

class ProtectPHITest extends TestCase
{
    use UserHelpers;

    protected $admin;
    protected $nurse;
    protected $patient;

    protected $practice;

    protected function setUp()
    {
        parent::setUp();

        $this->practice = factory(Practice::class)->create();
        $this->patient  = $this->createUser($this->practice->id, 'participant');

        //admin has the phi.read permission so we have to deactivate
        $this->admin = $this->createUser($this->practice->id, 'administrator');
        $phiRead     = Permission::whereName('phi.read')->first();
        $this->admin->attachPermission($phiRead->id, 0);
    }

    public function test_auth_user_cannot_see_phi_on_pages()
    {
        $this->login($this->admin);
        //visit careplan page
        //assert not see patient name
        $this->assertTrue(true);
    }

    /**
     *Find a way to test this if there are no routes.
     *
     * @return void
     */
    public function test_db_query_returns_hidden_phi_fields_to_auth_user()
    {
        $this->assertTrue(true);
    }
}
