<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Michalisantoniou6\Cerberus\Cerberus;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class CerberusTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;
    protected $abortFilterTest;
    protected $customResponseFilterTest;

    protected $expectedResponse;

    protected $nullFilterTest;

    public function setUp()
    {
        $this->nullFilterTest = function ($filterClosure) {
            if ( ! ($filterClosure instanceof Closure)) {
                return false;
            }

            $this->assertNull($filterClosure());

            return true;
        };

        $this->abortFilterTest = function ($filterClosure) {
            if ( ! ($filterClosure instanceof Closure)) {
                return false;
            }

            try {
                $filterClosure();
            } catch (Exception $e) {
                $this->assertSame('abort', $e->getMessage());

                return true;
            }

            // If we've made it this far, no exception was thrown and something went wrong
            return false;
        };

        $this->customResponseFilterTest = function ($filterClosure) {
            if ( ! ($filterClosure instanceof Closure)) {
                return false;
            }

            $result = $filterClosure();

            $this->assertSame($this->expectedResponse, $result);

            return true;
        };
    }

    public function routeNeedsRoleOrPermissionFilterDataProvider()
    {
        return [
            // Both role and permission pass, null is returned
            [true,  true,  'nullFilterTest'],
            [true,  true,  'nullFilterTest', true],
            // Role OR permission fail, require all is false, null is returned
            [false, true,  'nullFilterTest'],
            [true,  false, 'nullFilterTest'],
            // Role and/or permission fail, App::abort() is called
            [false, true,  'abortFilterTest', true,  true],
            [true,  false, 'abortFilterTest', true,  true],
            [false, false, 'abortFilterTest', false, true],
            [false, false, 'abortFilterTest', true,  true],
            // Role and/or permission fail, custom response is returned
            [false, true,  'customResponseFilterTest', true,  false, new stdClass()],
            [true,  false, 'customResponseFilterTest', true,  false, new stdClass()],
            [false, false, 'customResponseFilterTest', false, false, new stdClass()],
            [false, false, 'customResponseFilterTest', true,  false, new stdClass()],
        ];
    }

    public function simpleFilterDataProvider()
    {
        return [
            // Filter passes, null is returned
            [true, 'nullFilterTest'],
            // Filter fails, App::abort() is called
            [false, 'abortFilterTest', true],
            // Filter fails, custom response is returned
            [false, 'customResponseFilterTest', false, new stdClass()],
        ];
    }

    public function test_can()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $app      = new stdClass();
        $cerberus = m::mock('Michalisantoniou6\Cerberus\Cerberus[user]', [$app]);
        $user     = m::mock('_mockedUser');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $cerberus->shouldReceive('user')
            ->andReturn($user)
            ->twice()->ordered();

        $cerberus->shouldReceive('user')
            ->andReturn(false)
            ->once()->ordered();

        $user->shouldReceive('hasPermission')
            ->with('user_can', false)
            ->andReturn(true)
            ->once();

        $user->shouldReceive('hasPermission')
            ->with('user_cannot', false)
            ->andReturn(false)
            ->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($cerberus->hasPermission('user_can'));
        $this->assertFalse($cerberus->hasPermission('user_cannot'));
        $this->assertFalse($cerberus->hasPermission('any_permission'));
    }

    /**
     * @dataProvider simpleFilterDataProvider
     *
     * @param mixed      $returnValue
     * @param mixed      $filterTest
     * @param mixed      $abort
     * @param mixed|null $expectedResponse
     */
    public function test_filter_generated_by_route_needs_permission($returnValue, $filterTest, $abort = false, $expectedResponse = null)
    {
        $this->filterTestExecution('routeNeedsPermission', 'hasPermission', $returnValue, $filterTest, $abort, $expectedResponse);
    }

    /**
     * @dataProvider simpleFilterDataProvider
     *
     * @param mixed      $returnValue
     * @param mixed      $filterTest
     * @param mixed      $abort
     * @param mixed|null $expectedResponse
     */
    public function test_filter_generated_by_route_needs_role($returnValue, $filterTest, $abort = false, $expectedResponse = null)
    {
        $this->filterTestExecution('routeNeedsRole', 'hasRole', $returnValue, $filterTest, $abort, $expectedResponse);
    }

    /**
     * @dataProvider routeNeedsRoleOrPermissionFilterDataProvider
     *
     * @param mixed      $roleIsValid
     * @param mixed      $permIsValid
     * @param mixed      $filterTest
     * @param mixed      $requireAll
     * @param mixed      $abort
     * @param mixed|null $expectedResponse
     */
    public function test_filter_generated_by_route_needs_role_or_permission(
        $roleIsValid,
        $permIsValid,
        $filterTest,
        $requireAll = false,
        $abort = false,
        $expectedResponse = null
    ) {
        $app         = m::mock('Illuminate\Foundation\Application');
        $app->router = m::mock('Route');
        $cerberus    = m::mock('Michalisantoniou6\Cerberus\Cerberus[hasRole, hasPermission]', [$app]);

        // Static values
        $route      = 'route';
        $roleName   = 'UserRole';
        $permName   = 'user-permission';
        $filterName = $this->makeFilterName($route, [$roleName], [$permName]);

        $app->router->shouldReceive('when')->with($route, $filterName)->once();
        $app->router->shouldReceive('filter')->with($filterName, m::on($this->$filterTest))->once();

        $cerberus->shouldReceive('hasRole')->with($roleName, $requireAll)->andReturn($roleIsValid)->once();
        $cerberus->shouldReceive('hasPermission')->with($permName, $requireAll)->andReturn($permIsValid)->once();

        if ($abort) {
            $app->shouldReceive('abort')->with(403)->andThrow('Exception', 'abort')->once();
        }

        $this->expectedResponse = $expectedResponse;

        $cerberus->routeNeedsRoleOrPermission($route, $roleName, $permName, $expectedResponse, $requireAll);
    }

    public function test_has_role()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $app      = new stdClass();
        $cerberus = m::mock('Michalisantoniou6\Cerberus\Cerberus[user]', [$app]);
        $user     = m::mock('_mockedUser');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $cerberus->shouldReceive('user')
            ->andReturn($user)
            ->twice()->ordered();

        $cerberus->shouldReceive('user')
            ->andReturn(false)
            ->once()->ordered();

        $user->shouldReceive('hasRole')
            ->with('UserRole', false)
            ->andReturn(true)
            ->once();

        $user->shouldReceive('hasRole')
            ->with('NonUserRole', false)
            ->andReturn(false)
            ->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($cerberus->hasRole('UserRole'));
        $this->assertFalse($cerberus->hasRole('NonUserRole'));
        $this->assertFalse($cerberus->hasRole('AnyRole'));
    }

    public function test_route_needs_permission()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $app         = new stdClass();
        $app->router = m::mock('Route');
        $cerberus    = new Cerberus($app);

        $route    = 'route';
        $onePerm  = 'can_a';
        $manyPerm = ['can_a', 'can_b', 'can_c'];

        $onePermFilterName  = $this->makeFilterName($route, [$onePerm]);
        $manyPermFilterName = $this->makeFilterName($route, $manyPerm);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $app->router->shouldReceive('filter')
            ->with(m::anyOf($onePermFilterName, $manyPermFilterName), m::type('Closure'))
            ->twice()->ordered();

        $app->router->shouldReceive('when')
            ->with($route, m::anyOf($onePermFilterName, $manyPermFilterName))
            ->twice();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $cerberus->routeNeedsPermission($route, $onePerm);
        $cerberus->routeNeedsPermission($route, $manyPerm);
    }

    public function test_route_needs_role()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $app         = new stdClass();
        $app->router = m::mock('Route');
        $cerberus    = new Cerberus($app);

        $route    = 'route';
        $oneRole  = 'RoleA';
        $manyRole = ['RoleA', 'RoleB', 'RoleC'];

        $oneRoleFilterName  = $this->makeFilterName($route, [$oneRole]);
        $manyRoleFilterName = $this->makeFilterName($route, $manyRole);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $app->router->shouldReceive('filter')
            ->with(m::anyOf($oneRoleFilterName, $manyRoleFilterName), m::type('Closure'))
            ->twice()->ordered();

        $app->router->shouldReceive('when')
            ->with($route, m::anyOf($oneRoleFilterName, $manyRoleFilterName))
            ->twice();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $cerberus->routeNeedsRole($route, $oneRole);
        $cerberus->routeNeedsRole($route, $manyRole);
    }

    public function test_route_needs_role_or_permission()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $app         = new stdClass();
        $app->router = m::mock('Route');
        $cerberus    = new Cerberus($app);

        $route    = 'route';
        $oneRole  = 'RoleA';
        $manyRole = ['RoleA', 'RoleB', 'RoleC'];
        $onePerm  = 'can_a';
        $manyPerm = ['can_a', 'can_b', 'can_c'];

        $oneRoleOnePermFilterName   = $this->makeFilterName($route, [$oneRole], [$onePerm]);
        $oneRoleManyPermFilterName  = $this->makeFilterName($route, [$oneRole], $manyPerm);
        $manyRoleOnePermFilterName  = $this->makeFilterName($route, $manyRole, [$onePerm]);
        $manyRoleManyPermFilterName = $this->makeFilterName($route, $manyRole, $manyPerm);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $app->router->shouldReceive('filter')
            ->with(
                m::anyOf(
                    $oneRoleOnePermFilterName,
                    $oneRoleManyPermFilterName,
                    $manyRoleOnePermFilterName,
                    $manyRoleManyPermFilterName
                ),
                m::type('Closure')
            )
            ->times(4)->ordered();

        $app->router->shouldReceive('when')
            ->with(
                $route,
                m::anyOf(
                    $oneRoleOnePermFilterName,
                    $oneRoleManyPermFilterName,
                    $manyRoleOnePermFilterName,
                    $manyRoleManyPermFilterName
                )
            )
            ->times(4);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $cerberus->routeNeedsRoleOrPermission($route, $oneRole, $onePerm);
        $cerberus->routeNeedsRoleOrPermission($route, $oneRole, $manyPerm);
        $cerberus->routeNeedsRoleOrPermission($route, $manyRole, $onePerm);
        $cerberus->routeNeedsRoleOrPermission($route, $manyRole, $manyPerm);
    }

    public function test_user()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $app       = new stdClass();
        $app->auth = m::mock('Auth');
        $cerberus  = new Cerberus($app);
        $user      = m::mock('_mockedUser');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $app->auth->shouldReceive('user')
            ->andReturn($user)
            ->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertSame($user, $cerberus->user());
    }

    protected function filterTestExecution($methodTested, $mockedMethod, $returnValue, $filterTest, $abort, $expectedResponse)
    {
        // Mock Objects
        $app         = m::mock('Illuminate\Foundation\Application');
        $app->router = m::mock('Route');
        $cerberus    = m::mock("Michalisantoniou6\Cerberus\Cerberus[$mockedMethod]", [$app]);

        // Static values
        $route       = 'route';
        $methodValue = 'role-or-permission';
        $filterName  = $this->makeFilterName($route, [$methodValue]);

        $app->router->shouldReceive('when')->with($route, $filterName)->once();
        $app->router->shouldReceive('filter')->with($filterName, m::on($this->$filterTest))->once();

        if ($abort) {
            $app->shouldReceive('abort')->with(403)->andThrow('Exception', 'abort')->once();
        }

        $this->expectedResponse = $expectedResponse;

        $cerberus->shouldReceive($mockedMethod)->with($methodValue, m::any(true, false))->andReturn($returnValue)->once();
        $cerberus->$methodTested($route, $methodValue, $expectedResponse);
    }

    protected function makeFilterName($route, array $roles, array $permissions = null)
    {
        if (is_null($permissions)) {
            return implode('_', $roles).'_'.substr(md5($route), 0, 6);
        }

        return implode('_', $roles).'_'.implode('_', $permissions).'_'.substr(md5($route), 0, 6);
    }
}
