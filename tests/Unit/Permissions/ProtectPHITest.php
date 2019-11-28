<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Enrollee;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Customer\Entities\Permission;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Mockery as m;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;

class ProtectPHITest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;
    use UserHelpers;
    protected $admin;
    protected $enrollee;
    protected $info;
    protected $nurse;
    protected $patient;

    protected $practice;
    protected $provider;

    private $facadeMocks = [];

    protected function setUp()
    {
        parent::setUp();
        //could not make it work
//        $app = m::mock('app')->shouldReceive('instance')->getMock();
//        $this->facadeMocks['config'] = m::mock('config');
//        $this->facadeMocks['cache']  = m::mock('cache');
//
//        Config::setFacadeApplication($app);
//        Config::swap($this->facadeMocks['config']);
//
//        Cache::setFacadeApplication($app);
//        Cache::swap($this->facadeMocks['cache']);

        $this->practice = factory(Practice::class)->create();

        //admin has the phi.read permission so we have to deactivate
        $this->admin = $this->createUser($this->practice->id, 'administrator');
        $this->disablePHIForUser($this->admin);

        $this->patient  = $this->createUser($this->practice->id, 'participant');
        $this->enrollee = factory(Enrollee::class)->create()->first();
    }

    public function test_auth_user_cannot_see_phi_on_pages()
    {
        auth()->login($this->admin);
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
        //TODO: add when feature completed CPM-1819
        $this->assertTrue(true);
    }

    public function test_trait_hides_phi_fields_correctly()
    {
        //User model
        $this->assertHiddenPhiForModel($this->patient);

        $this->assertHiddenPhiForModel($this->patient->patientInfo);

        $this->assertHiddenPhiForModel($this->enrollee);
    }

    private function assertHiddenPhiForModel($model)
    {
        $model->setShouldHidePhi(true);
        foreach ($model->phi as $phiField) {
            $this->assertEquals($this->getExpectedValueForKey($model, $phiField), $model->$phiField);
        }
    }

    private function disablePHIForUser(User $user)
    {
        $phiRead = Permission::whereName('phi.read')->first();
        $user->attachPermission($phiRead, 0);
        $this->assertTrue( ! $user->hasPermission('phi.read'));
    }

    private function getExpectedValueForKey($model, $phiField)
    {
        $phiCasts = [
            'date'  => Carbon::parse('9999-01-01'),
            'array' => [],
        ];

        if (array_key_exists($phiField, $model->getCasts())) {
            return $phiCasts[$model->getCasts()[$phiField]];
        }
        if (in_array($phiField, $model->getDates())) {
            return $phiCasts['date'];
        }

        return '***';
    }

    /**
     * The idea here was to get all models dynamically and loop over them, checking each one.
     * TODO: FIX models missing + handle sql view models after retrieving them.
     */
    private function getModelsThatContainPhi(): array
    {
        $models = [];
        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, BaseModel::class)) {
                $model = new $class();
                if ( ! empty($model->phi)) {
                    $models[] = $model;
                }
            }
        }

        return $models;
    }
}
