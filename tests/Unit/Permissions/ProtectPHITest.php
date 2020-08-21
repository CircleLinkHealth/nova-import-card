<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Traits\Tests\UserHelpers;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Mockery as m;
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

    protected $patientPhi;

    protected $practice;
    protected $provider;

    private $facadeMocks = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->practice = factory(Practice::class)->create();

        $this->admin = $this->createUser($this->practice->id, 'administrator');
        //admin has the phi.read permission so we have to deactivate
        $this->disablePHIForUser($this->admin);

        $this->patient = $this->createUser($this->practice->id, 'participant');

        //add fixed names to prevent random failures
        $this->patient->first_name = 'Testfirstname';
        $this->patient->last_name  = 'Testlastname';
        $this->patient->save();

        $this->patientPhi = $this->collectPatientPhiValues();

        $this->enrollee = factory(Enrollee::class)->create();
    }

    public function test_auth_user_cannot_see_phi_on_pages()
    {
        //login
        auth()->login($this->admin);

        //care-plan
        $this->assertAuthUserCannotSeePatientPhi(
            $this->actingAs($this->admin)->call('GET', route('patient.careplan.print', [
                'patientId' => $this->patient->id,
            ]))
        );

        //patient listing
        $this->assertAuthUserCannotSeePatientPhi(
            $this->actingAs($this->admin)->call('GET', route('patients.listing'))
        );

        //careplan print list
        $this->assertAuthUserCannotSeePatientPhi(
            $this->actingAs($this->admin)->call('GET', route('patients.careplan.printlist'))
        );

        //under 20 mins report
        $this->assertAuthUserCannotSeePatientPhi(
            $this->actingAs($this->admin)->call('GET', route('patient.reports.u20'))
        );

        //notes
        $this->assertAuthUserCannotSeePatientPhi(
            $this->actingAs($this->admin)->call('GET', route('patient.note.index', [
                'patientId' => $this->patient->id,
            ]))
        );

        //profile
        $this->assertAuthUserCannotSeePatientPhi(
            $this->actingAs($this->admin)->call('GET', route('patient.demographics.show', [
                'patientId' => $this->patient->id,
            ]))
        );

        //progress
        $this->assertAuthUserCannotSeePatientPhi(
            $this->actingAs($this->admin)->call('GET', route('patient.reports.progress', [
                'patientId' => $this->patient->id,
            ]))
        );

        //wellness visit docs
        $this->assertAuthUserCannotSeePatientPhi(
            $this->actingAs($this->admin)->call('GET', route('patient.care-docs', [
                'patientId' => $this->patient->id,
            ]))
        );

        //activities
        $this->assertAuthUserCannotSeePatientPhi(
            $this->actingAs($this->admin)->call('GET', route('patient.care-docs', [
                'patientId' => $this->patient->id,
            ]))
        );
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
        auth()->login($this->admin);
        //User model
        $this->assertHiddenPhiForModel($this->patient);

        $this->assertHiddenPhiForModel($this->patient->patientInfo);

        $this->assertHiddenPhiForModel($this->enrollee);
    }

    private function assertAuthUserCannotSeePatientPhi($response)
    {
        $response->assertOk();

        foreach ($this->patientPhi as $phi) {
            if (is_a($phi, Carbon::class)) {
                $phi = $phi->toDateString();
            }
            if (in_array($phi, ['MD', 'AL'])) {
                //this is the suffix and state of the logged in user generated for admins too by default
                //remember that logged in User can see their own PHI field values.
                continue;
            }

            //even if logged in user has no access to PHI, their own name will be displayed on top nav-bar
            //sometimes with faker data, a first or last name happens to be the same with the patient's and the tests fail.
            //this is the easiest way to resolve this. We could also check patient names upon seeding the data but that would slow down the test suite
            if (in_array($phi, [
                $this->admin->first_name,
                $this->admin->last_name,
            ])) {
                continue;
            }
            $response->assertDontSee($phi);
        }
    }

    private function assertHiddenPhiForModel($model)
    {
        $model->setShouldHidePhi(true);
        foreach ($model->phi as $phiField) {
            $this->assertEquals($this->getExpectedValueForKey($model, $phiField), $model->$phiField);
        }
    }

    private function collectPatientPhiValues()
    {
        $patientPhi = [];
        foreach ($this->patient->phi as $phi) {
            $patientPhi[] = $this->patient->$phi;
        }
        foreach ($this->patient->patientInfo->phi as $phi) {
            $patientPhi[] = $this->patient->patientInfo->$phi;
        }

        return collect($patientPhi)->filter();
    }

    private function disablePHIForUser(User $user)
    {
        $user->setCanSeePhi(false);
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
