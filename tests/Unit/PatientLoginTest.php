<?php

namespace Tests\Unit;

use App\Notifications\NotifyPatientCarePlanApproved;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
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

        $this->assertTrue($this->featureIsEnabledForPatient());

        $this->setupPatientEssentialDetails();
    }

    /**
     * Test notification is sent to patient upon both QA and Provider Care Plan Approvals
     *
     * @return void
     */
    public function test_notification_is_sent_after_cp_qa_approval()
    {
        Notification::fake();

        //Nurse QA approves patient Care plan
        $this->qaApproveCarePlan();

        Notification::assertSentTo(
            $this->patient,
            NotifyPatientCarePlanApproved::class,
            function ($notification, $channels) {
                $this->call('GET', $notification->resetUrl($this->patient))
                     ->assertOk();

                $mailData = $notification->toMail($this->patient)->toArray();

                $this->assertEquals("Your Care Plan has been sent to your doctor for approval", $mailData['subject']);

                return true;
            }
        );

    }

    /**
     *
     */
    public function test_notification_is_sent_after_cp_provider_approval(){
        Notification::fake();

        $this->patient->carePlan->status = CarePlan::QA_APPROVED;
        $this->patient->carePlan->save();

        $this->patient->load('carePlan');

        //Provider approves patient Care pLAN
        $this->providerApprovesCarePlan();

        Notification::assertSentTo(
            $this->patient,
            NotifyPatientCarePlanApproved::class,
            function ($notification, $channels) {
                $this->call('GET', $notification->resetUrl($this->patient))
                     ->assertOk();

                $mailData = $notification->toMail($this->patient)->toArray();

                $this->assertEquals('Your CarePlan has just been approved', $mailData['subject']);

                return true;
            }
        );
    }

    /**
     *
     */
    public function test_provider_cannot_go_to_patient_page()
    {
        $this->actingAs($this->provider);

        $this->call('GET', route('patient-user.careplan'))
             ->assertStatus(302)
             ->assertRedirect(url('/login'))
             ->assertSessionHasErrors();

        $this->assertEquals(session('errors')->get(0)[0], 'This page can be accessed only by patients.');
    }

    /**
     *
     */
    public function test_patient_cannot_login_if_feature_disabled()
    {
        $this->disableFeatureForPatient();

        $this->actingAs($this->patient);

        $this->assertFalse($this->featureIsEnabledForPatient());
        $this->assertFalse(patientLoginIsEnabledForPractice($this->patient->program_id));

        $this->call('GET', route('patient-user.careplan'))
             ->assertRedirect(url('/login'))
             ->assertSessionHasErrors();

        $this->assertEquals(session('errors')->get(0)[0], 'This feature has not been enabled by your Provider yet.');
    }

    /**
     *
     */
    public function test_patient_cannot_go_to_page_if_careplan_is_draft()
    {
        $this->actingAs($this->patient);

        $this->enableFeatureForPatient();

        $this->assertEquals($this->patient->carePlan->status, CarePlan::DRAFT);

        $this->call('GET', route('patient-user.careplan'))
             ->assertRedirect(url('/login'))
             ->assertSessionHasErrors();

        $this->assertEquals(session('errors')->get('careplan-error')[0],
            "Your Care Plan is being reviewed. <br> For details, please contact CircleLink Health Support at <a href='mailto:contact@circlelinkhealth.com'>contact@circlelinkhealth.com</a>.");
    }

    /**
     *
     */
    public function test_patient_can_login_and_see_their_care_plan()
    {
        $this->qaApproveCarePlan();

        $this->actingAs($this->patient);

        $this->call('GET', route('home'))
             ->assertRedirect(route('patient-user.careplan'));

        $this->call('GET', route('patient-user.careplan'))
             ->assertSeeText('This Care Plan is pending Dr. approval')
             ->assertSeeText($this->patient->first_name)
             ->assertSeeText($this->patient->last_name);

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
     * @return mixed
     */
    private function featureIsEnabledForPatient()
    {
        return AppConfig::whereConfigKey('enable_patient_login_for_practice')
                        ->whereConfigValue($this->patient->program_id)
                        ->exists();
    }

    /**
     *
     */
    private function qaApproveCarePlan()
    {
        $this->actingAs($this->nurse);

        $this->assertEquals(CarePlan::DRAFT, $this->patient->carePlan->status);

        $this->call('POST', route('patient.careplan.approve', [
            'patientId' => $this->patient->id,
        ]), [
            //in case patient has both types of diabetes
            'confirm_diabetes_conditions' => 1,
        ])
             ->assertSessionHasNoErrors();

        auth()->logout();

        $this->patient->load('carePlan');
        //assert careplan has been QA approved
        $this->assertEquals($this->patient->carePlan->status, CarePlan::QA_APPROVED);
    }

    /**
     * Log in as provider and approve careplan
     */
    private function providerApprovesCarePlan()
    {
        $this->actingAs($this->provider);

        $this->call('POST', route('patient.careplan.approve', [
            'patientId' => $this->patient->id,
        ]))
             ->assertSessionHasNoErrors();

        auth()->logout();

        $this->patient->load('carePlan');

        $this->assertEquals($this->patient->carePlan->status, CarePlan::PROVIDER_APPROVED);
    }
}
