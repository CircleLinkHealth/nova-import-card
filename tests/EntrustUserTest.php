<?php

use Illuminate\Cache\ArrayStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Michalisantoniou6\Entrust\Contracts\EntrustUserInterface;
use Michalisantoniou6\Entrust\Permission;
use Michalisantoniou6\Entrust\Role;
use Michalisantoniou6\Entrust\Traits\EntrustUserTrait;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class EntrustUserTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private $facadeMocks = [];

    public function setUp()
    {
        parent::setUp();

        $app = m::mock('app')->shouldReceive('instance')->getMock();

        $this->facadeMocks['config'] = m::mock('config');
        $this->facadeMocks['cache']  = m::mock('cache');
        $this->facadeMocks['db']     = m::spy('db');

        Config::setFacadeApplication($app);
        Config::swap($this->facadeMocks['config']);

        Cache::setFacadeApplication($app);
        Cache::swap($this->facadeMocks['cache']);

        DB::setFacadeApplication($app);
        DB::swap($this->facadeMocks['db']);
    }

    public function testRoles()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $belongsToMany = m::mock('BelongsToMany');
        $user          = m::mock('HasRoleUser')->makePartial();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $belongsToMany->shouldReceive('withPivot')
                      ->andReturn($belongsToMany);

        $user->shouldReceive('belongsToMany')
             ->with('role_table_name', 'assigned_roles_table_name', 'user_id', 'role_id')
             ->andReturn($belongsToMany)
             ->once();

        Config::shouldReceive('get')->once()->with('entrust.role')
              ->andReturn('role_table_name');
        Config::shouldReceive('get')->once()->with('entrust.role_user_site_table')
              ->andReturn('assigned_roles_table_name');
        Config::shouldReceive('get')->once()->with('entrust.user_foreign_key')
              ->andReturn('user_id');
        Config::shouldReceive('get')->once()->with('entrust.role_foreign_key')
              ->andReturn('role_id');
        Config::shouldReceive('get')->once()->with('entrust.site_foreign_key')
              ->andReturn('site_id');

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertSame($belongsToMany, $user->roles());
    }

    public function testHasRole()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $roleA = $this->mockRole('RoleA');
        $roleB = $this->mockRole('RoleB');

        $user        = new HasRoleUser();
        $user->roles = [$roleA, $roleB];

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        Config::shouldReceive('get')->with('entrust.role_user_site_table')->times(9)->andReturn('role_user_site');
        Config::shouldReceive('get')->with('cache.ttl')->times(9)->andReturn('1440');
        Cache::shouldReceive('tags->remember')->times(9)->andReturn($user->roles);
        Cache::shouldReceive('getStore')->times(9)->andReturn(new ArrayStore);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $site = 1;
        $this->assertTrue($user->hasRole('RoleA', false, $site));
        $this->assertTrue($user->hasRole('RoleB', false, $site));
        $this->assertFalse($user->hasRole('RoleC', false, $site));

        $this->assertTrue($user->hasRole(['RoleA', 'RoleB'], false, $site));
        $this->assertTrue($user->hasRole(['RoleA', 'RoleC'], false, $site));
        $this->assertFalse($user->hasRole(['RoleA', 'RoleC'], true, $site));
        $this->assertFalse($user->hasRole(['RoleC', 'RoleD'], false, $site));
    }

    protected function mockRole($roleName)
    {
        $roleMock              = m::mock('Michalisantoniou6\Entrust\Role');
        $roleMock->name        = $roleName;
        $roleMock->perms       = [];
        $roleMock->permissions = [];
        $roleMock->id          = 1;

        return $roleMock;
    }

    public function testCan()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $permA = $this->mockPermission('manage_a');
        $permB = $this->mockPermission('manage_b');
        $permC = $this->mockPermission('manage_c');

        $roleA = $this->mockRole('RoleA');
        $roleB = $this->mockRole('RoleB');

        $roleA->perms = [$permA];
        $roleB->perms = [$permB, $permC];

        $user        = new HasRoleUser();
        $user->roles = [$roleA, $roleB];

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $roleA->shouldReceive('cachedPermissions')->times(11)->andReturn($roleA->perms);
        $roleB->shouldReceive('cachedPermissions')->times(7)->andReturn($roleB->perms);
        Config::shouldReceive('get')->with('entrust.role_user_site_table')->times(11)->andReturn('role_user_site');
        Config::shouldReceive('get')->with('cache.ttl')->times(11)->andReturn('1440');
        Cache::shouldReceive('tags->remember')->times(11)->andReturn($user->roles);
        Cache::shouldReceive('getStore')->times(11)->andReturn(new ArrayStore);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($user->can('manage_a'));
        $this->assertTrue($user->can('manage_b'));
        $this->assertTrue($user->can('manage_c'));
        $this->assertFalse($user->can('manage_d'));

        $this->assertTrue($user->can(['manage_a', 'manage_b', 'manage_c']));
        $this->assertTrue($user->can(['manage_a', 'manage_b', 'manage_d']));
        $this->assertFalse($user->can(['manage_a', 'manage_b', 'manage_d'], true));
        $this->assertFalse($user->can(['manage_d', 'manage_e']));
    }

    protected function mockPermission($permName)
    {
        $permMock               = m::mock('Michalisantoniou6\Entrust\Permission');
        $permMock->name         = $permName;
        $permMock->display_name = ucwords(str_replace('_', ' ', $permName));
        $permMock->id           = 1;

        return $permMock;
    }

    public function testCanWithPlaceholderSupport()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $permA = $this->mockPermission('admin.posts');
        $permB = $this->mockPermission('admin.pages');
        $permC = $this->mockPermission('admin.users');

        $role = $this->mockRole('Role');

        $role->perms = [$permA, $permB, $permC];

        $user        = new HasRoleUser();
        $user->roles = [$role];

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $role->shouldReceive('cachedPermissions')->times(6)->andReturn($role->perms);
        Config::shouldReceive('get')->with('entrust.role_user_site_table')->times(6)->andReturn('role_user_site');
        Config::shouldReceive('get')->with('cache.ttl')->times(6)->andReturn('1440');
        Cache::shouldReceive('tags->remember')->times(6)->andReturn($user->roles);
        Cache::shouldReceive('getStore')->times(6)->andReturn(new ArrayStore);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($user->can('admin.posts'));
        $this->assertTrue($user->can('admin.pages'));
        $this->assertTrue($user->can('admin.users'));
        $this->assertFalse($user->can('admin.config'));

        $this->assertTrue($user->can(['admin.*']));
        $this->assertFalse($user->can(['site.*']));
    }

    public function testAbilityShouldReturnBoolean()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $userPermNameA    = 'user_can_a';
        $userPermNameB    = 'user_can_b';
        $userPermNameC    = 'user_can_c';
        $nonUserPermNameA = 'user_cannot_a';
        $nonUserPermNameB = 'user_cannot_b';
        $userRoleNameA    = 'UserRoleA';
        $userRoleNameB    = 'UserRoleB';
        $nonUserRoleNameA = 'NonUserRoleA';
        $nonUserRoleNameB = 'NonUserRoleB';

        $permA = $this->mockPermission($userPermNameA);
        $permB = $this->mockPermission($userPermNameB);
        $permC = $this->mockPermission($userPermNameC);

        $roleA = $this->mockRole($userRoleNameA);
        $roleB = $this->mockRole($userRoleNameB);

        $roleA->perms = [$permA];
        $roleB->perms = [$permB, $permC];

        $user             = m::mock('HasRoleUser')->makePartial();
        $user->roles      = [$roleA, $roleB];
        $user->id         = 4;
        $user->primaryKey = 'id';

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $roleA->shouldReceive('cachedPermissions')->times(16)->andReturn($roleA->perms);
        $roleB->shouldReceive('cachedPermissions')->times(12)->andReturn($roleB->perms);
        Config::shouldReceive('get')->with('entrust.role_user_site_table')->times(16)->andReturn('role_user_site');
        Config::shouldReceive('get')->with('cache.ttl')->times(16)->andReturn('1440');
        Cache::shouldReceive('tags->remember')->times(16)->andReturn($user->roles);
        Cache::shouldReceive('getStore')->times(16)->andReturn(new ArrayStore);

        $site = 1;

        $user->shouldReceive('hasRole')
             ->with(m::anyOf($userRoleNameA, $userRoleNameB), m::anyOf(true, false), $site)
             ->andReturn(true);
        $user->shouldReceive('hasRole')
             ->with(m::anyOf($nonUserRoleNameA, $nonUserRoleNameB), m::anyOf(true, false), $site)
             ->andReturn(false);
        $user->shouldReceive('can')
             ->with(m::anyOf($userPermNameA, $userPermNameB, $userPermNameC), m::anyOf(true, false), $site)
             ->andReturn(true);
        $user->shouldReceive('can')
             ->with(m::anyOf($nonUserPermNameA, $nonUserPermNameB), m::anyOf(true, false), $site)
             ->andReturn(false);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        // Case: User has everything.
        $this->assertTrue(
            $user->ability(
                [$userRoleNameA, $userRoleNameB],
                [$userPermNameA, $userPermNameB],
                ['validate_all' => false],
                $site
            )
        );
        $this->assertTrue(
            $user->ability(
                [$userRoleNameA, $userRoleNameB],
                [$userPermNameA, $userPermNameB],
                ['validate_all' => true],
                $site
            )
        );

        // Case: User lacks a role.
        $this->assertTrue(
            $user->ability(
                [$nonUserRoleNameA, $userRoleNameB],
                [$userPermNameA, $userPermNameB],
                ['validate_all' => false],
                $site
            )
        );
        $this->assertFalse(
            $user->ability(
                [$nonUserRoleNameA, $userRoleNameB],
                [$userPermNameA, $userPermNameB],
                ['validate_all' => true],
                $site
            )
        );

        // Case: User lacks a permission.
        $this->assertTrue(
            $user->ability(
                [$userRoleNameA, $userRoleNameB],
                [$nonUserPermNameA, $userPermNameB],
                ['validate_all' => false],
                $site
            )
        );
        $this->assertFalse(
            $user->ability(
                [$userRoleNameA, $userRoleNameB],
                [$nonUserPermNameA, $userPermNameB],
                ['validate_all' => true],
                $site
            )
        );

        // Case: User lacks everything.
        $this->assertFalse(
            $user->ability(
                [$nonUserRoleNameA, $nonUserRoleNameB],
                [$nonUserPermNameA, $nonUserPermNameB],
                ['validate_all' => false],
                $site
            )
        );
        $this->assertFalse(
            $user->ability(
                [$nonUserRoleNameA, $nonUserRoleNameB],
                [$nonUserPermNameA, $nonUserPermNameB],
                ['validate_all' => true],
                $site
            )
        );
    }

    public function testAbilityShouldReturnArray()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $userPermNameA    = 'user_can_a';
        $userPermNameB    = 'user_can_b';
        $userPermNameC    = 'user_can_c';
        $nonUserPermNameA = 'user_cannot_a';
        $nonUserPermNameB = 'user_cannot_b';
        $userRoleNameA    = 'UserRoleA';
        $userRoleNameB    = 'UserRoleB';
        $nonUserRoleNameA = 'NonUserRoleA';
        $nonUserRoleNameB = 'NonUserRoleB';

        $permA = $this->mockPermission($userPermNameA);
        $permB = $this->mockPermission($userPermNameB);
        $permC = $this->mockPermission($userPermNameC);

        $roleA = $this->mockRole($userRoleNameA);
        $roleB = $this->mockRole($userRoleNameB);

        $roleA->perms = [$permA];
        $roleB->perms = [$permB, $permC];

        $user             = m::mock('HasRoleUser')->makePartial();
        $user->roles      = [$roleA, $roleB];
        $user->id         = 4;
        $user->primaryKey = 'id';


        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $roleA->shouldReceive('cachedPermissions')->times(16)->andReturn($roleA->perms);
        $roleB->shouldReceive('cachedPermissions')->times(12)->andReturn($roleB->perms);
        Config::shouldReceive('get')->with('entrust.role_user_site_table')->times(32)->andReturn('role_user_site');
        Config::shouldReceive('get')->with('cache.ttl')->times(32)->andReturn('1440');
        Cache::shouldReceive('tags->remember')->times(32)->andReturn($user->roles);
        Cache::shouldReceive('getStore')->times(32)->andReturn(new ArrayStore);

        $user->shouldReceive('hasRole')
             ->with(m::anyOf($userRoleNameA, $userRoleNameB), m::anyOf(true, false))
             ->andReturn(true);
        $user->shouldReceive('hasRole')
             ->with(m::anyOf($nonUserRoleNameA, $nonUserRoleNameB), m::anyOf(true, false))
             ->andReturn(false);
        $user->shouldReceive('can')
             ->with(m::anyOf($userPermNameA, $userPermNameB, $userPermNameC), m::anyOf(true, false))
             ->andReturn(true);
        $user->shouldReceive('can')
             ->with(m::anyOf($nonUserPermNameA, $nonUserPermNameB), m::anyOf(true, false))
             ->andReturn(false);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $site = 1;
        // Case: User has everything.
        $this->assertSame(
            [
                'roles'       => [$userRoleNameA => true, $userRoleNameB => true],
                'permissions' => [$userPermNameA => true, $userPermNameB => true],
            ],
            $user->ability(
                [$userRoleNameA, $userRoleNameB],
                [$userPermNameA, $userPermNameB],
                ['return_type' => 'array'],
                $site
            )
        );
        $this->assertSame(
            [
                'roles'       => [$userRoleNameA => true, $userRoleNameB => true],
                'permissions' => [$userPermNameA => true, $userPermNameB => true],
            ],
            $user->ability(
                [$userRoleNameA, $userRoleNameB],
                [$userPermNameA, $userPermNameB],
                ['validate_all' => true, 'return_type' => 'array'],
                $site
            )
        );


        // Case: User lacks a role.
        $this->assertSame(
            [
                'roles'       => [$nonUserRoleNameA => false, $userRoleNameB => true],
                'permissions' => [$userPermNameA => true, $userPermNameB => true],
            ],
            $user->ability(
                [$nonUserRoleNameA, $userRoleNameB],
                [$userPermNameA, $userPermNameB],
                ['return_type' => 'array'],
                $site
            )
        );
        $this->assertSame(
            [
                'roles'       => [$nonUserRoleNameA => false, $userRoleNameB => true],
                'permissions' => [$userPermNameA => true, $userPermNameB => true],
            ],
            $user->ability(
                [$nonUserRoleNameA, $userRoleNameB],
                [$userPermNameA, $userPermNameB],
                ['validate_all' => true, 'return_type' => 'array'],
                $site
            )
        );


        // Case: User lacks a permission.
        $this->assertSame(
            [
                'roles'       => [$userRoleNameA => true, $userRoleNameB => true],
                'permissions' => [$nonUserPermNameA => false, $userPermNameB => true],
            ],
            $user->ability(
                [$userRoleNameA, $userRoleNameB],
                [$nonUserPermNameA, $userPermNameB],
                ['return_type' => 'array'],
                $site
            )
        );
        $this->assertSame(
            [
                'roles'       => [$userRoleNameA => true, $userRoleNameB => true],
                'permissions' => [$nonUserPermNameA => false, $userPermNameB => true],
            ],
            $user->ability(
                [$userRoleNameA, $userRoleNameB],
                [$nonUserPermNameA, $userPermNameB],
                ['validate_all' => true, 'return_type' => 'array'],
                $site
            )
        );


        // Case: User lacks everything.
        $this->assertSame(
            [
                'roles'       => [$nonUserRoleNameA => false, $nonUserRoleNameB => false],
                'permissions' => [$nonUserPermNameA => false, $nonUserPermNameB => false],
            ],
            $user->ability(
                [$nonUserRoleNameA, $nonUserRoleNameB],
                [$nonUserPermNameA, $nonUserPermNameB],
                ['return_type' => 'array'],
                $site
            )
        );
        $this->assertSame(
            [
                'roles'       => [$nonUserRoleNameA => false, $nonUserRoleNameB => false],
                'permissions' => [$nonUserPermNameA => false, $nonUserPermNameB => false],
            ],
            $user->ability(
                [$nonUserRoleNameA, $nonUserRoleNameB],
                [$nonUserPermNameA, $nonUserPermNameB],
                ['validate_all' => true, 'return_type' => 'array'],
                $site
            )
        );
    }

    public function testAbilityShouldReturnBoth()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $userPermNameA    = 'user_can_a';
        $userPermNameB    = 'user_can_b';
        $userPermNameC    = 'user_can_c';
        $nonUserPermNameA = 'user_cannot_a';
        $nonUserPermNameB = 'user_cannot_b';
        $userRoleNameA    = 'UserRoleA';
        $userRoleNameB    = 'UserRoleB';
        $nonUserRoleNameA = 'NonUserRoleA';
        $nonUserRoleNameB = 'NonUserRoleB';

        $permA = $this->mockPermission($userPermNameA);
        $permB = $this->mockPermission($userPermNameB);
        $permC = $this->mockPermission($userPermNameC);

        $roleA = $this->mockRole($userRoleNameA);
        $roleB = $this->mockRole($userRoleNameB);

        $roleA->perms = [$permA];
        $roleB->perms = [$permB, $permC];

        $user             = m::mock('HasRoleUser')->makePartial();
        $user->roles      = [$roleA, $roleB];
        $user->id         = 4;
        $user->primaryKey = 'id';

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $roleA->shouldReceive('cachedPermissions')->times(16)->andReturn($roleA->perms);
        $roleB->shouldReceive('cachedPermissions')->times(12)->andReturn($roleB->perms);
        Config::shouldReceive('get')->with('entrust.role_user_site_table')->times(32)->andReturn('role_user_site');
        Config::shouldReceive('get')->with('cache.ttl')->times(32)->andReturn('1440');
        Cache::shouldReceive('tags->remember')->times(32)->andReturn($user->roles);
        Cache::shouldReceive('getStore')->times(32)->andReturn(new ArrayStore);

        $user->shouldReceive('hasRole')
             ->with(m::anyOf($userRoleNameA, $userRoleNameB), m::anyOf(true, false))
             ->andReturn(true);
        $user->shouldReceive('hasRole')
             ->with(m::anyOf($nonUserRoleNameA, $nonUserRoleNameB), m::anyOf(true, false))
             ->andReturn(false);
        $user->shouldReceive('can')
             ->with(m::anyOf($userPermNameA, $userPermNameB, $userPermNameC), m::anyOf(true, false))
             ->andReturn(true);
        $user->shouldReceive('can')
             ->with(m::anyOf($nonUserPermNameA, $nonUserPermNameB), m::anyOf(true, false))
             ->andReturn(false);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $site = 1;
        // Case: User has everything.
        $this->assertSame(
            [
                true,
                [
                    'roles'       => [$userRoleNameA => true, $userRoleNameB => true],
                    'permissions' => [$userPermNameA => true, $userPermNameB => true],
                ],
            ],
            $user->ability(
                [$userRoleNameA, $userRoleNameB],
                [$userPermNameA, $userPermNameB],
                ['return_type' => 'both'],
                $site
            )
        );
        $this->assertSame(
            [
                true,
                [
                    'roles'       => [$userRoleNameA => true, $userRoleNameB => true],
                    'permissions' => [$userPermNameA => true, $userPermNameB => true],
                ],
            ],
            $user->ability(
                [$userRoleNameA, $userRoleNameB],
                [$userPermNameA, $userPermNameB],
                ['validate_all' => true, 'return_type' => 'both'],
                $site
            )
        );


        // Case: User lacks a role.
        $this->assertSame(
            [
                true,
                [
                    'roles'       => [$nonUserRoleNameA => false, $userRoleNameB => true],
                    'permissions' => [$userPermNameA => true, $userPermNameB => true],
                ],
            ],
            $user->ability(
                [$nonUserRoleNameA, $userRoleNameB],
                [$userPermNameA, $userPermNameB],
                ['return_type' => 'both'],
                $site
            )
        );
        $this->assertSame(
            [
                false,
                [
                    'roles'       => [$nonUserRoleNameA => false, $userRoleNameB => true],
                    'permissions' => [$userPermNameA => true, $userPermNameB => true],
                ],
            ],
            $user->ability(
                [$nonUserRoleNameA, $userRoleNameB],
                [$userPermNameA, $userPermNameB],
                ['validate_all' => true, 'return_type' => 'both'],
                $site
            )
        );


        // Case: User lacks a permission.
        $this->assertSame(
            [
                true,
                [
                    'roles'       => [$userRoleNameA => true, $userRoleNameB => true],
                    'permissions' => [$nonUserPermNameA => false, $userPermNameB => true],
                ],
            ],
            $user->ability(
                [$userRoleNameA, $userRoleNameB],
                [$nonUserPermNameA, $userPermNameB],
                ['return_type' => 'both'],
                $site
            )
        );
        $this->assertSame(
            [
                false,
                [
                    'roles'       => [$userRoleNameA => true, $userRoleNameB => true],
                    'permissions' => [$nonUserPermNameA => false, $userPermNameB => true],
                ],
            ],
            $user->ability(
                [$userRoleNameA, $userRoleNameB],
                [$nonUserPermNameA, $userPermNameB],
                ['validate_all' => true, 'return_type' => 'both'],
                $site
            )
        );


        // Case: User lacks everything.
        $this->assertSame(
            [
                false,
                [
                    'roles'       => [$nonUserRoleNameA => false, $nonUserRoleNameB => false],
                    'permissions' => [$nonUserPermNameA => false, $nonUserPermNameB => false],
                ],
            ],
            $user->ability(
                [$nonUserRoleNameA, $nonUserRoleNameB],
                [$nonUserPermNameA, $nonUserPermNameB],
                ['return_type' => 'both'],
                $site
            )
        );
        $this->assertSame(
            [
                false,
                [
                    'roles'       => [$nonUserRoleNameA => false, $nonUserRoleNameB => false],
                    'permissions' => [$nonUserPermNameA => false, $nonUserPermNameB => false],
                ],
            ],
            $user->ability(
                [$nonUserRoleNameA, $nonUserRoleNameB],
                [$nonUserPermNameA, $nonUserPermNameB],
                ['validate_all' => true, 'return_type' => 'both'],
                $site
            )
        );
    }

    public function testAbilityShouldAcceptStrings()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $permA = $this->mockPermission('user_can_a');
        $permB = $this->mockPermission('user_can_b');
        $permC = $this->mockPermission('user_can_c');

        $roleA = $this->mockRole('UserRoleA');
        $roleB = $this->mockRole('UserRoleB');

        $roleA->perms = [$permA];
        $roleB->perms = [$permB, $permC];

        $user             = m::mock('HasRoleUser')->makePartial();
        $user->roles      = [$roleA, $roleB];
        $user->id         = 4;
        $user->primaryKey = 'id';

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $roleA->shouldReceive('cachedPermissions')->times(4)->andReturn($roleA->perms);
        $roleB->shouldReceive('cachedPermissions')->times(2)->andReturn($roleB->perms);
        Config::shouldReceive('get')->with('entrust.role_user_site_table')->times(8)->andReturn('role_user_site');
        Config::shouldReceive('get')->with('cache.ttl')->times(8)->andReturn('1440');
        Cache::shouldReceive('tags->remember')->times(8)->andReturn($user->roles);
        Cache::shouldReceive('getStore')->times(8)->andReturn(new ArrayStore);

        $user->shouldReceive('hasRole')
             ->with(m::anyOf('UserRoleA', 'UserRoleB'), m::anyOf(true, false))
             ->andReturn(true);
        $user->shouldReceive('hasRole')
             ->with('NonUserRoleB', m::anyOf(true, false))
             ->andReturn(false);
        $user->shouldReceive('can')
             ->with(m::anyOf('user_can_a', 'user_can_b', 'user_can_c'), m::anyOf(true, false))
             ->andReturn(true);
        $user->shouldReceive('can')
             ->with('user_cannot_b', m::anyOf(true, false))
             ->andReturn(false);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $site = 1;
        $this->assertSame(
            $user->ability(
                ['UserRoleA', 'NonUserRoleB'],
                ['user_can_a', 'user_cannot_b'],
                ['return_type' => 'both'],
                $site
            ),
            $user->ability(
                'UserRoleA,NonUserRoleB',
                'user_can_a,user_cannot_b',
                ['return_type' => 'both'],
                $site
            )
        );
    }

    public function testAbilityDefaultOptions()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $userPermNameA    = 'user_can_a';
        $userPermNameB    = 'user_can_b';
        $userPermNameC    = 'user_can_c';
        $nonUserPermNameA = 'user_cannot_a';
        $nonUserPermNameB = 'user_cannot_b';
        $userRoleNameA    = 'UserRoleA';
        $userRoleNameB    = 'UserRoleB';
        $nonUserRoleNameA = 'NonUserRoleA';
        $nonUserRoleNameB = 'NonUserRoleB';

        $permA = $this->mockPermission($userPermNameA);
        $permB = $this->mockPermission($userPermNameB);
        $permC = $this->mockPermission($userPermNameC);

        $roleA = $this->mockRole($userRoleNameA);
        $roleB = $this->mockRole($userRoleNameB);

        $roleA->perms = [$permA];
        $roleB->perms = [$permB, $permC];

        $user             = m::mock('HasRoleUser')->makePartial();
        $user->roles      = [$roleA, $roleB];
        $user->id         = 4;
        $user->primaryKey = 'id';

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $roleA->shouldReceive('cachedPermissions')->times(16)->andReturn($roleA->perms);
        $roleB->shouldReceive('cachedPermissions')->times(12)->andReturn($roleB->perms);
        Config::shouldReceive('get')->with('entrust.role_user_site_table')->times(32)->andReturn('role_user_site');
        Config::shouldReceive('get')->with('cache.ttl')->times(32)->andReturn('1440');
        Cache::shouldReceive('tags->remember')->times(32)->andReturn($user->roles);
        Cache::shouldReceive('getStore')->times(32)->andReturn(new ArrayStore);

        $user->shouldReceive('hasRole')
             ->with(m::anyOf($userRoleNameA, $userRoleNameB), m::anyOf(true, false))
             ->andReturn(true);
        $user->shouldReceive('hasRole')
             ->with(m::anyOf($nonUserRoleNameA, $nonUserRoleNameB), m::anyOf(true, false))
             ->andReturn(false);
        $user->shouldReceive('can')
             ->with(m::anyOf($userPermNameA, $userPermNameB, $userPermNameC), m::anyOf(true, false))
             ->andReturn(true);
        $user->shouldReceive('can')
             ->with(m::anyOf($nonUserPermNameA, $nonUserPermNameB), m::anyOf(true, false))
             ->andReturn(false);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $site = 1;
        // Case: User has everything.
        $this->assertSame(
            $user->ability(
                [$userRoleNameA, $userRoleNameB],
                [$userPermNameA, $userPermNameB],
                [],
                $site
            ),
            $user->ability(
                [$userRoleNameA, $userRoleNameB],
                [$userPermNameA, $userPermNameB],
                ['validate_all' => false, 'return_type' => 'boolean'],
                $site
            )
        );


        // Case: User lacks a role.
        $this->assertSame(
            $user->ability(
                [$nonUserRoleNameA, $userRoleNameB],
                [$userPermNameA, $userPermNameB],
                [],
                $site
            ),
            $user->ability(
                [$nonUserRoleNameA, $userRoleNameB],
                [$userPermNameA, $userPermNameB],
                ['validate_all' => false, 'return_type' => 'boolean'],
                $site
            )
        );


        // Case: User lacks a permission.
        $this->assertSame(
            $user->ability(
                [$userRoleNameA, $userRoleNameB],
                [$nonUserPermNameA, $userPermNameB],
                [],
                $site
            ),
            $user->ability(
                [$userRoleNameA, $userRoleNameB],
                [$nonUserPermNameA, $userPermNameB],
                ['validate_all' => false, 'return_type' => 'boolean'],
                $site
            )
        );


        // Case: User lacks everything.
        $this->assertSame(
            $user->ability(
                [$nonUserRoleNameA, $nonUserRoleNameB],
                [$nonUserPermNameA, $nonUserPermNameB],
                [],
                $site
            ),
            $user->ability(
                [$nonUserRoleNameA, $nonUserRoleNameB],
                [$nonUserPermNameA, $nonUserPermNameB],
                ['validate_all' => false, 'return_type' => 'boolean'],
                $site
            )
        );
    }

    public function testAbilityShouldThrowInvalidArgumentException()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $permA = $this->mockPermission('manage_a');

        $roleA        = $this->mockRole('RoleA');
        $roleA->perms = [$permA];

        $user             = m::mock('HasRoleUser')->makePartial();
        $user->roles      = [$roleA];
        $user->id         = 4;
        $user->primaryKey = 'id';

        function isExceptionThrown(
            HasRoleUser $user,
            array $roles,
            array $perms,
            array $options,
            $site
        ) {
            $isExceptionThrown = false;

            try {
                $user->ability($roles, $perms, $options, $site);
            } catch (InvalidArgumentException $e) {
                $isExceptionThrown = true;
            }

            return $isExceptionThrown;
        }

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $user->shouldReceive('hasRole')
             ->times(3);
        $user->shouldReceive('can')
             ->times(3);

        $site = 1;
        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertFalse(isExceptionThrown($user, ['RoleA'], ['manage_a'], ['return_type' => 'boolean'], $site));
        $this->assertFalse(isExceptionThrown($user, ['RoleA'], ['manage_a'], ['return_type' => 'array'], $site));
        $this->assertFalse(isExceptionThrown($user, ['RoleA'], ['manage_a'], ['return_type' => 'both'], $site));
        $this->assertTrue(isExceptionThrown($user, ['RoleA'], ['manage_a'], ['return_type' => 'potato'], $site));
    }

    public function testAttachRole()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $roleObject = m::mock('Role');
        $roleArray  = ['id' => 2];

        $user          = m::mock('HasRoleUser')->makePartial();
        $belongsToMany = m::mock('BelongsToMany');

        $site = 1;


        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        Config::shouldReceive('get')->with('entrust.site_foreign_key')->times(3)->andReturn('site_id');

        $roleObject->shouldReceive('getKey')
                   ->andReturn(1);

        $user->shouldReceive('roles')
             ->andReturn($belongsToMany);

        $belongsToMany->shouldReceive('attach')
                      ->with(1, ['site_id' => $site])
                      ->once()->ordered();
        $belongsToMany->shouldReceive('attach')
                      ->with(2, ['site_id' => $site])
                      ->once()->ordered();
        $belongsToMany->shouldReceive('attach')
                      ->with(3, ['site_id' => $site])
                      ->once()->ordered();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $user->attachRole($roleObject, $site);
        $user->attachRole($roleArray, $site);
        $user->attachRole(3, $site);
    }

    public function testDetachRole()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $belongsToMany = m::mock('BelongsToMany');
        $roleObject    = m::mock(Michalisantoniou6\Entrust\EntrustRole::class);
        $roleArray     = ['id' => 2];

        $user = m::mock('HasRoleUser')->makePartial();

        $site = 1;

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        Config::shouldReceive('get')->with('entrust.role_foreign_key')->times(3)->andReturn('role_id');
        Config::shouldReceive('get')->with('entrust.site_foreign_key')->times(3)->andReturn('site_id');
        Config::shouldReceive('get')->with('entrust.role_user_site_table')->times(3)->andReturn('role_user_site');

        $roleObject->shouldReceive('getKey')
                   ->times(1)
                   ->andReturn(1);

        DB::shouldHaveReceived('table')->shouldHaveReceived('where');

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $user->detachRole($roleObject, $site);
        $user->detachRole($roleArray, $site);
        $user->detachRole(3, $site);
    }

    public function testAttachRoles()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $user = m::mock('HasRoleUser')->makePartial();
        $belongsToMany = m::mock('BelongsToMany');


        $site = 1;
        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        Config::shouldReceive('get')->with('entrust.role')->andReturn('App\Role');
        Config::shouldReceive('get')->with('entrust.user_foreign_key')->andReturn('user_id');
        Config::shouldReceive('get')->with('entrust.role_foreign_key')->andReturn('role_id');
        Config::shouldReceive('get')->with('entrust.site_foreign_key')->andReturn('site_id');
        Config::shouldReceive('get')->with('entrust.role_user_site_table')->andReturn('role_user_site');

        $user->shouldReceive('roles')
             ->andReturn($belongsToMany);

        $belongsToMany->shouldReceive('attach')
                      ->with(1, ['site_id' => $site])
                      ->once()->ordered();
        $belongsToMany->shouldReceive('attach')
                      ->with(2, ['site_id' => $site])
                      ->once()->ordered();
        $belongsToMany->shouldReceive('attach')
                      ->with(3, ['site_id' => $site])
                      ->once()->ordered();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $user->attachRoles([1, 2, 3], $site);
    }

    public function testDetachRoles()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $user = m::mock('HasRoleUser')->makePartial();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $user->shouldReceive('detachRole')
             ->with(1)
             ->once()->ordered();
        $user->shouldReceive('detachRole')
             ->with(2)
             ->once()->ordered();
        $user->shouldReceive('detachRole')
             ->with(3)
             ->once()->ordered();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $user->detachRoles([1, 2, 3]);
    }

    public function testDetachAllRoles()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $roleA = $this->mockRole('RoleA');
        $roleB = $this->mockRole('RoleB');

        $user        = m::mock('HasRoleUser')->makePartial();
        $user->roles = [$roleA, $roleB];

        $relationship = m::mock('BelongsToMany');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        Config::shouldReceive('get')->with('entrust.role')->once()->andReturn('App\Role');
        Config::shouldReceive('get')->with('entrust.role_user_site_table')->once()->andReturn('role_user_site');
        Config::shouldReceive('get')->with('entrust.user_foreign_key')->once()->andReturn('user_id');
        Config::shouldReceive('get')->with('entrust.role_foreign_key')->once()->andReturn('role_id');

        $relationship->shouldReceive('get')
                     ->andReturn($user->roles)->once();

        $user->shouldReceive('belongsToMany')
             ->andReturn($relationship)->once();

        $user->shouldReceive('detachRole')->twice();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $user->detachRoles();

    }
}

class HasRoleUser implements EntrustUserInterface
{
    use EntrustUserTrait;

    public $roles;
    public $pivotColumns = ['site_id'];
    public $primaryKey;
    public $id;

    public function __construct()
    {
        $this->primaryKey = 'id';
        $this->id         = 4;
    }

    public function belongsToMany($role, $assignedRolesTable)
    {
        return new BelongsToMany();
    }
}

class BelongsToMany
{
    public function withPivot()
    {

    }

    public function attach($id, array $attributes = [])
    {

    }
}
