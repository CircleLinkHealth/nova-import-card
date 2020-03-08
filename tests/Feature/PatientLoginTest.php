<?php

namespace Tests\Feature;

use App\Notifications\NotifyPatientCarePlanApproved;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
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

        //create care center that can QA approve careplans
        $careCoach = $this->careCoach();
        auth()->login($careCoach);

        $carePlan = $this->patient->carePlan;

        $this->assertEquals(CarePlan::DRAFT, $carePlan->status);

        $this->call('POST', route('patient.careplan.approve', [
            'patientId' => $this->patient->id,
        ]), [
            //in case patient has both types of diabetes
            'confirm_diabetes_conditions' => 1,
        ])->assertSessionHasNoErrors();

        //assert careplan has been QA approved
        $this->assertEquals($carePlan->fresh()->status, CarePlan::QA_APPROVED);

        Notification::assertSentTo(
            $this->patient,
            NotifyPatientCarePlanApproved::class,
            function ($notification, $channels){
                return $this->call('GET', $notification->resetUrl())
                            ->assertOk()
                    ->assertSee($this->patient->getPrimaryPracticeName());
            }
        );

        //enable feature
//        assert that patient receives notification after careplan approval
//assert notification is sent and contains needed information
//assert that notification does not contain CLH branding of any kind
//assert that middleware that redirects patient if data is false works
//assert that patient cannot see data they are not supposed to see.
//                                                             assert clh cannot be seen in patient page
//
//assert that patient upon visiting login or password reset page -> they cannot see CLH branding.
//                                                                                      assert that Practice branding does not work for users other than patient.
//
//                                                                                                                                                       $response = $this->get('/');
//
//        $response->assertStatus(200);
    }

    /**
     *
     */
    public function test_patient_can_login_and_see_their_care_plan()
    {

    }

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

    private function enableFeatureForPatient(){
        AppConfig::create([
            'config_key' => 'enable_patient_login_for_practice',
            'config_value' => $this->patient->program_id
        ]);
    }
}
