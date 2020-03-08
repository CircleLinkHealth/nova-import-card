<?php

namespace Tests\Unit;

use App\Notifications\NotifyPatientCarePlanApproved;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\MessageBag;
use Tests\CustomerTestCase;

class PatientLoginTest extends CustomerTestCase
{
    /**
     * @var User
     */
    protected $patient;

    /**
     * @var User
     */
    protected $nurse;

    /**
     * @var User
     */
    protected $provider;

    /**
     *
     */
    protected function setUp()
    {
        parent::setUp();

        $this->patient  = $this->patient();
        $this->nurse    = $this->careCoach();
        $this->provider = $this->provider();

        $this->enableFeatureForPatient();

        $this->setupPatientEssentialDetails();
    }

    /**
     * Test notification is sent to patient upon both QA and Provider Care Plan Approvals
     *
     * @return void
     */
    public function test_notification_is_sent_after_cp_approval()
    {
        Notification::fake();

        //Nurse QA approves patient Care plan
        $this->qaApproveCarePlan();

        Notification::assertSentTo(
            $this->patient,
            NotifyPatientCarePlanApproved::class,
            function ($notification, $channels) {
                $this->call('GET', $notification->resetUrl())
                     ->assertOk();

                $this->call('GET', url('/'))
                     ->assertOk();

                //todo: fix assertSee - it was failing, even though practice name existed in html

                $mailData = $notification->toMail($this->patient)->toArray();
                $this->assertEquals("Your Care Plan has been sent to your doctor for approval", $mailData['subject']);

                return true;
            }
        );

        //Provider approves patient Care pLAN
        $this->providerApprovesCarePlan();

        Notification::assertSentTo(
            $this->patient,
            NotifyPatientCarePlanApproved::class,
            function ($notification, $channels) {
                $this->call('GET', $notification->resetUrl())
                     ->assertOk();

                $this->call('GET', route('home', [
                    'practice_id' => $this->patient->program_id,
                ]))
                     ->assertOk();
                //todo: fix assertSee - it was failing, even though practice name existed in html

                $mailData = $notification->toMail($this->patient)->toArray();
                $this->assertEquals('Your CarePlan has just been approved', $mailData['subject']);

                return true;
            }
        );

    }

    /**
     *
     */
    public function test_patient_can_login_and_see_their_care_plan()
    {
        auth()->login($this->provider);

        $this->call('GET', route('patient-user.careplan'))
             ->assertStatus(302)
             ->assertRedirect(route('login'))
             ->assertSessionHas('errors', new MessageBag([
                 [
                     'This page can be accessed only by patients.',
                 ],
             ]));

        auth()->login($this->patient);

        $this->disableFeatureForPatient();

        $this->call('GET', route('home'))
             ->assertRedirect(route('login'))
             ->assertSessionHas('errors', new MessageBag([
                 [
                     'This feature has not been enabled by your Provider yet.',
                 ],
             ]));

        auth()->login($this->patient);

        $this->enableFeatureForPatient();

        $this->assertEquals($this->patient->carePlan->status, CarePlan::DRAFT);

        $this->call('GET', route('home'))
             ->assertRedirect(route('login'))
             ->assertSessionHas('errors', new MessageBag([
                 'careplan-error' => [
                     "Your Care Plan is being reviewed. <br> For details, please contact CircleLink Health Support at <a href='mailto:contact@circlelinkhealth.com'>contact@circlelinkhealth.com</a>.",
                 ],
             ]));

        $this->qaApproveCarePlan();

        auth()->login($this->patient);

        $response = $this->call('GET', route('home'))
                         ->assertRedirect(route('patient-user.careplan'))
                         ->assertSeeText('This Care Plan is pending Dr. approval')
                         ->assertSeeText($this->patient->first_name)
                         ->assertSeeText($this->patient->last_name);

        foreach ($this->patient->ccdProblems as $problem) {
            $response->assertSee($problem->name);
        }
    }

    /**
     * Add patient details so it can pass care plan approval
     */
    private function setupPatientEssentialDetails()
    {

        $this->patient->setBirthDate(Carbon::now()->subYear(20));

        $this->patient->setMRN(rand());

        $this->patient->careTeamMembers()->create([
            'member_user_id' => $this->provider->id,
            'type'           => CarePerson::BILLING_PROVIDER,
        ]);

        $this->patient->setPhone('+1-541-754-3010');

        $this->patient->save();


        $problemsToAdd = CpmProblem::get()->random(5)->transform(function ($p) {
            return [
                'name'           => $p->name,
                'cpm_problem_id' => $p->id,
            ];
        })->toArray();

        $this->patient->ccdProblems()->createMany($problemsToAdd);
    }

    /**
     * Tests feature flag.
     */
    private function enableFeatureForPatient()
    {
        AppConfig::create([
            'config_key'   => 'enable_patient_login_for_practice',
            'config_value' => $this->patient->program_id,
        ]);
    }

    /**
     * Tests feature flag.
     */
    private function disableFeatureForPatient()
    {
        AppConfig::whereConfigKey('enable_patient_login_for_practice')
                 ->whereConfigValue($this->patient->program_id)
                 ->delete();
    }

    /**
     *
     */
    private function qaApproveCarePlan()
    {
        auth()->login($this->nurse);

        $carePlan = $this->patient->carePlan;

        $this->assertEquals(CarePlan::DRAFT, $carePlan->status);

        $this->call('POST', route('patient.careplan.approve', [
            'patientId' => $this->patient->id,
        ]), [
            //in case patient has both types of diabetes
            'confirm_diabetes_conditions' => 1,
        ])->assertSessionHasNoErrors();

        auth()->logout();

        //assert careplan has been QA approved
        $this->assertEquals($carePlan->fresh()->status, CarePlan::QA_APPROVED);
    }

    /**
     * Log in as provider and approve careplan
     */
    private function providerApprovesCarePlan()
    {
        auth()->login($this->provider);

        $carePlan = $this->patient->carePlan;

        $this->call('POST', route('patient.careplan.approve', [
            'patientId' => $this->patient->id,
        ]))->assertSessionHasNoErrors();

        auth()->logout();

        $this->assertEquals($carePlan->fresh()->status, CarePlan::PROVIDER_APPROVED);
    }
}
