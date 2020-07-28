<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Notifications\CarePlanProviderApproved;
use App\Traits\Tests\UserHelpers;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use CircleLinkHealth\SharedModels\Rules\DoesNotHaveBothTypesOfDiabetes;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Tests\Concerns\PhaxioFake\Phaxio;
use Tests\Concerns\TwilioFake\Twilio;
use Tests\CustomerTestCase;
use Tests\Helpers\CarePlanHelpers;

class OnCarePlanProviderApprovalTest extends CustomerTestCase
{
    use CarePlanHelpers;
    use UserHelpers;

    /**
     * @var CarePlan
     */
    protected $carePlan;

    /**
     * @var Location
     */
    protected $location;

    /**
     * @var
     */
    protected $patient;

    /**
     * @var Practice
     */
    protected $practice;

    /**
     * @var \CircleLinkHealth\Customer\Entities\User
     */
    protected $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $settings                    = $this->practice()->cpmSettings();
        $settings->efax_pdf_careplan = true;
        $settings->dm_pdf_careplan   = true;
        $settings->save();

        $this->patient()->setPreferredContactLocation($this->location()->id);

        $this->carePlan = $this->patient()->carePlan;
    }

    public function test_careplan_validation()
    {
        $validator = $this->carePlan->validator();

        $this->assertTrue($validator->fails());
        $this->assertTrue('The Care Plan must have two CPM problems for CCM, one if practice has PCM (G2065) enabled or one BHI problem.' === $validator->errors()->first('conditions'), $validator->errors()->first('conditions'));
        $this->assertTrue('The mrn field is required.' === $validator->errors()->first('mrn'), $validator->errors()->first('mrn'));
        $this->assertTrue('The billing provider field is required.' === $validator->errors()->first('billingProvider'), $validator->errors()->first('billingProvider'));

        //remove diabetes to prevent randomly failing test
        //validation preventing both diabetes conditions is addressed in:
        //test_qa_approval_is_blocked_for_patients_with_both_types_of_diabetes_unless_approver_confirms
        $cpmProblems = CpmProblem::whereNotIn(
            'name',
            [
                CpmProblem::DIABETES_TYPE_1,
                CpmProblem::DIABETES_TYPE_2,
            ]
        )->get();

        $ccdProblems = $this->patient()->ccdProblems()->createMany(
            [
                ['name' => 'test'.Str::random(5), 'cpm_problem_id' => $cpmProblems->random()->id, 'is_monitored' => true],
                ['name' => 'test'.Str::random(5), 'cpm_problem_id' => $cpmProblems->random()->id, 'is_monitored' => true],
                ['name' => 'test'.Str::random(5), 'cpm_problem_id' => $cpmProblems->random()->id, 'is_monitored' => true],
                ['name' => 'test'.Str::random(5), 'cpm_problem_id' => $cpmProblems->random()->id, 'is_monitored' => true],
                ['name' => 'test'.Str::random(5), 'cpm_problem_id' => $cpmProblems->random()->id, 'is_monitored' => true],
            ]
        );

        foreach ($ccdProblems as $problem) {
            $problem->cpmProblem()->associate($cpmProblems->random());
            $problem->save();
        }

        //add each one individually and check for error messages
        $this->patient()->setBirthDate(Carbon::now()->subYear(20));

        $this->patient()->setMRN(rand());

        $this->patient()->careTeamMembers()->create(
            [
                'member_user_id' => $this->provider()->id,
                'type'           => CarePerson::BILLING_PROVIDER,
            ]
        );

        $this->patient()->setPhone('+1-541-754-3010');

        $this->patient()->save();

        $validator = $this->carePlan->validator();

        $this->assertTrue($validator->passes(), $validator->errors()->toJson());
    }

    /**
     * Test that a CarePlan is forwarded to a location.
     */
    public function test_it_forwards_careplan_to_location()
    {
        Notification::fake();

        $this->carePlan->forward();

        Notification::assertSentTo(
            $this->patient()->patientInfo->location,
            CarePlanProviderApproved::class
        );
    }

    public function test_it_forwards_careplan_when_provider_approved()
    {
        $this->carePlan->status = CarePlan::RN_APPROVED;
        $this->carePlan->save();

        $response = $this->actingAs($this->provider())->call(
            'POST',
            route('patient.careplan.approve', ['patientId' => $this->patient()->id])
        );

        $response->assertStatus(302);
        $response->assertRedirect(
            route(
                'patient.careplan.print',
                [
                    'patientId'    => $this->patient()->id,
                    'clearSession' => false,
                ]
            )
        );

        $this->carePlan = $this->carePlan->fresh();

        $this->assertEquals(1, $this->carePlan->notifications()->count());

        $this->assertEquals($this->carePlan->status, CarePlan::PROVIDER_APPROVED);
        $this->assertEquals($this->carePlan->provider_approver_id, $this->provider()->id);
        $this->assertTrue(Carbon::now()->isSameDay($this->carePlan->provider_date));
    }

    public function test_qa_approval_is_blocked_for_patients_with_both_types_of_diabetes_unless_approver_confirms()
    {
        //add patient essential info for validation to pass
        $this->patient()->setBirthDate(Carbon::now()->subYear(20));
        $this->patient()->setMRN(rand());
        $this->patient()->careTeamMembers()->create(
            [
                'member_user_id' => $this->provider()->id,
                'type'           => CarePerson::BILLING_PROVIDER,
            ]
        );
        $this->patient()->setPhone('+1-541-754-3010');
        $this->patient()->save();

        //add both diabetes problems
        $this->patient()->ccdProblems()->createMany(
            [
                [
                    'name'           => 'diabetes1',
                    'cpm_problem_id' => CpmProblem::whereName(CpmProblem::DIABETES_TYPE_1)->first()->id,
                ],
                [
                    'name'           => 'diabetes2',
                    'cpm_problem_id' => CpmProblem::whereName(CpmProblem::DIABETES_TYPE_2)->first()->id,
                ],
            ]
        );

        //Patient has both types of diabetes and DRAFT careplan. Test validation fails
        $this->assertFalse($this->carePlan->validator()->passes());
        //Test validation passes if approver confirms both types of diabetes are correct
        $this->assertTrue($this->carePlan->validator(true)->passes());

        auth()->login($this->superadmin());
        $carePlan = $this->carePlan;
        $this->assertEquals($carePlan->status, CarePlan::DRAFT);

        //set previous url to assert redirect, call route and assert session errors
        session()->setPreviousUrl(
            route(
                'patient.careplan.print',
                [
                    'patientId' => $this->patient()->id,
                ]
            )
        );
        $this->call('POST', route('patient.careplan.approve', ['patientId' => $this->patient()->id]))
            ->assertStatus(302)
            ->assertRedirect(
                route(
                    'patient.careplan.print',
                    [
                        'patientId' => $this->patient()->id,
                    ]
                )
            )
            ->assertSessionHas(
                'errors',
                new MessageBag(
                    [
                        'conditions' => [
                            (new DoesNotHaveBothTypesOfDiabetes())->message(),
                        ],
                    ]
                )
            );

        //assert careplan has not been approved
        $this->assertEquals($carePlan->fresh()->status, CarePlan::DRAFT);

        //call the same route with approver confirmation that patient has indeed both types of diabetes
        session()->setPreviousUrl(
            route(
                'patient.careplan.print',
                [
                    'patientId' => $this->patient()->id,
                ]
            )
        );
        $this->call(
            'POST',
            route(
                'patient.careplan.approve',
                [
                    'patientId' => $this->patient()->id,
                ]
            ),
            [
                'confirm_diabetes_conditions' => 1,
            ]
        )->assertSessionHasNoErrors();

        //assert careplan has been QA approved
        $this->assertEquals($carePlan->fresh()->status, CarePlan::QA_APPROVED);
    }
}
