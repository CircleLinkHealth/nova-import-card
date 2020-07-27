<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Browser;

use App\Traits\Tests\UserHelpers;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Permission;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Helpers\CarePlanHelpers;

class OnCarePlanApprovalDuskTest extends DuskTestCase
{
    use CarePlanHelpers;
    use UserHelpers;

    /**
     * @var CarePlan
     */
    protected $carePlan;

    /**
     * @var array|User
     */
    private $careCoach;

    /**
     * @var Location
     */
    private $location;

    /**
     * @var array|User
     */
    private $patient;

    /**
     * @var Practice
     */
    private $practice;

    protected function setUp(): void
    {
        parent::setUp();
        $this->carePlan = $this->patient()->carePlan;
    }

    public function test_care_center_can_rn_approve()
    {
        static::assertTrue($this->careCoach()->canRNApproveCarePlans());
        $this->carePlan->status = CarePlan::QA_APPROVED;
        $this->carePlan->save();
        static::assertTrue(CarePlan::QA_APPROVED === $this->carePlan->fresh()->status);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->careCoach());
            $url = route('patient.careplan.print', ['patientId' => $this->patient()->id]);
            $browser->visit($url)->waitForText('Ready For Dr.');
        });
    }

    public function test_care_center_cannot_approve()
    {
        $carePlan = $this->carePlan;

        $carePlan->status = CarePlan::RN_APPROVED;
        $carePlan->save();
        $this->assertTrue(CarePlan::RN_APPROVED === $carePlan->fresh()->status);
        $this->assertFalse($this->careCoach()->canApproveCarePlans());

        $response = $this->actingAs($this->careCoach())->get(
            route(
                'patient.careplan.print',
                [
                    'patientId' => $this->patient()->id,
                ]
            )
        )
            ->assertDontSee('Approve');
    }

    public function test_medical_assistant_can_approve()
    {
        $medicalAssistant = $this->createUser($this->practice()->id, 'med_assistant');
        auth()->login($medicalAssistant);

        $this->carePlan->status = CarePlan::QA_APPROVED;
        $this->carePlan->save();

        $response = $this->get(
            route(
                'patient.careplan.print',
                [
                    'patientId' => $this->patient()->id,
                ]
            )
        )
            ->assertSee('Approve');
    }

    public function test_provider_can_approve()
    {
        $this->carePlan->status = CarePlan::QA_APPROVED;
        $this->carePlan->save();

        $response = $this->actingAs($this->provider())->get(
            route(
                'patient.careplan.print',
                [
                    'patientId' => $this->patient()->id,
                ]
            )
        )
            ->assertSee('Approve');
    }

    public function test_provider_can_approve_all_practice_careplans()
    {
        $this->carePlan->status = CarePlan::QA_APPROVED;
        $this->carePlan->save();
        $this->patient()->setBillingProviderId($this->provider(2)[0]->id);

        $this->assertNotEquals($this->provider(2)[0]->id, $this->provider(2)[1]->id);

        $this->assertTrue(1 === User::patientsPendingProviderApproval($this->provider(2)[0])->count());
        $this->assertTrue(1 === User::patientsPendingProviderApproval($this->provider(2)[1])->count());

        $response = $this->actingAs($this->provider(2)[1])->get(
            route(
                'patient.careplan.print',
                [
                    'patientId' => $this->patient()->id,
                ]
            )
        )
            ->assertSee('Approve');
    }

    public function test_provider_cannot_qa_approve()
    {
        $response = $this->actingAs($this->provider())->get(
            route(
                'patient.careplan.print',
                [
                    'patientId' => $this->patient()->id,
                ]
            )
        )
            ->assertDontSee('Approve');
    }

    public function test_r_n_can_approve()
    {
        $rn = $this->createUser($this->practice()->id, 'registered-nurse');

        $careplanApprove = Permission::where('name', 'care-plan-approve')->first();
        $rn->attachPermission($careplanApprove->id);

        auth()->login($rn);

        $this->carePlan->status = CarePlan::QA_APPROVED;
        $this->carePlan->save();

        $response = $this->get(
            route(
                'patient.careplan.print',
                [
                    'patientId' => $this->patient()->id,
                ]
            )
        )
            ->assertSee('Approve');
    }

    /**
     * @return array|User
     */
    protected function careCoach(int $number = 1)
    {
        if ( ! $this->careCoach) {
            $this->careCoach = $this->createUsersOfType('care-center', $number);
        }

        return $this->careCoach;
    }

    /**
     * @return Location
     */
    protected function location()
    {
        if ( ! $this->location) {
            $this->location = Location::where('practice_id', $this->practice()->id)->first();
            if ( ! $this->location) {
                $this->location = factory(Location::class)->create(['practice_id' => $this->practice()->id]);
            }
        }

        return $this->location;
    }

    /**
     * @return array|User
     */
    protected function patient(int $number = 1)
    {
        if ( ! $this->patient) {
            $this->patient = $this->createUsersOfType('participant', $number);
        }

        return $this->patient;
    }

    /**
     * @return Practice
     */
    protected function practice()
    {
        if ( ! $this->practice) {
            $this->practice = factory(Practice::class)->create();
            $this->location();
        }

        return $this->practice;
    }

    /**
     * @return array|User
     */
    private function createUsersOfType(string $roleName, int $number = 1)
    {
        if ($number > 1) {
            for ($i = 1; $i <= $number; ++$i) {
                $users[] = $this->createUser($this->practice()->id, $roleName);
            }
        } else {
            $users = $this->createUser($this->practice()->id, $roleName);
        }

        return $users;
    }
}
