<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Call;
use App\Http\Controllers\NotesController;
use App\Note;
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
    protected $nurse;
    /**
     * @var User
     */
    protected $patient;

    /**
     * @var User
     */
    protected $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->patient  = $this->patient();
        $this->nurse    = $this->careCoach();
        $this->provider = $this->provider();

        $this->enableFeatureForPatient();

        $this->assertTrue($this->featureIsEnabledForPatient());

        $this->setupPatientEssentialDetails();
    }

    public function test_notification_is_sent_after_cp_provider_approval()
    {
        Notification::fake();

        //CarePlan Observer will attempt to send the same notification for QA approval.
        $this->patient->carePlan->status = CarePlan::RN_APPROVED;
        $this->patient->carePlan->save();

        //However, we need to make assertions for the same notification sent for Provider Approval (different content)
        //if we do not reset the fake Notification bound instance here, the assertion will be made for the QA approval and it will fail
        //So: re-set Notification Fake
        Notification::fake();

        //Provider approves patient Care Plan
        $this->providerApprovesCarePlan();

        Notification::assertSentTo(
            $this->patient,
            NotifyPatientCarePlanApproved::class,
            function ($notification, $channels) {
                $this->call('GET', $notification->resetUrl($this->patient))
                    ->assertOk();

                $mailData = $notification->toMail($this->patient)->toArray();

                $this->assertTrue('Your Care Plan has just been approved' === $mailData['subject']);

                return true;
            }
        );
    }

    /**
     * Test notification is sent to patient upon RN Care Plan Approvals.
     *
     * @return void
     */
    public function test_notification_is_sent_after_cp_rn_approval()
    {
        Notification::fake();

        //Nurse QA approves patient Care plan
        $this->rnApproveCarePlan();

        Notification::assertSentTo(
            $this->patient,
            NotifyPatientCarePlanApproved::class,
            function ($notification, $channels) {
                $this->call('GET', $notification->resetUrl($this->patient))
                    ->assertOk();

                $mailData = $notification->toMail($this->patient)->toArray();

                $this->assertEquals('Your Care Plan has been sent to your doctor for approval', $mailData['subject']);

                return true;
            }
        );
    }

    public function test_patient_can_login_and_see_their_care_plan()
    {
        $this->rnApproveCarePlan();

        $this->flushSession();
        $this->actingAs($this->patient);

        $resp = $this->call('GET', route('home'));

        $resp->assertRedirect(route('patient-user.careplan'));

        $this->call('GET', route('patient-user.careplan'))
            ->assertSeeText('This Care Plan is pending Dr. approval')
            ->assertSeeText($this->patient->first_name)
            ->assertSeeText($this->patient->first_name);
    }

    public function test_patient_cannot_go_to_page_if_careplan_is_draft()
    {
        $this->actingAs($this->patient);

        $this->enableFeatureForPatient();

        $this->assertEquals($this->patient->carePlan->status, CarePlan::DRAFT);

        $this->call('GET', route('patient-user.careplan'))
            ->assertRedirect(url('/login'))
            ->assertSessionHasErrors();

        $this->assertEquals(
            session('errors')->get('careplan-error')[0],
            "Your Care Plan is being reviewed. <br> For details, please contact CircleLink Health Support at <a href='mailto:contact@circlelinkhealth.com'>contact@circlelinkhealth.com</a>."
        );
    }

    public function test_patient_cannot_go_to_page_if_careplan_is_qa_approved()
    {
        $this->actingAs($this->patient);

        $this->enableFeatureForPatient();

        $this->patient->carePlan->status = CarePlan::QA_APPROVED;
        $this->patient->carePlan->save();
        $this->patient->carePlan->fresh();
        $this->assertEquals($this->patient->carePlan->status, CarePlan::QA_APPROVED);

        $this->call('GET', route('patient-user.careplan'))
            ->assertRedirect(url('/login'))
            ->assertSessionHasErrors();

        $this->assertEquals(
            session('errors')->get('careplan-error')[0],
            "Your Care Plan is being reviewed. <br> For details, please contact CircleLink Health Support at <a href='mailto:contact@circlelinkhealth.com'>contact@circlelinkhealth.com</a>."
        );
    }

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

    public function test_provider_cannot_go_to_patient_page()
    {
        $this->actingAs($this->provider);

        $this->call('GET', route('patient-user.careplan'))
            ->assertStatus(302)
            ->assertRedirect(url('/login'))
            ->assertSessionHasErrors();

        $this->assertEquals(session('errors')->get(0)[0], 'This page can be accessed only by patients.');
    }

    private function createNote($patientId, $body)
    {
        /** @var NotesController $controller */
        $controller = app(NotesController::class);

        $resp = $this->call(
            'POST',
            route('patient.note.store', ['patientId' => $patientId]),
            [
                'status'      => 'complete',
                'type'        => 'CCM Welcome Call',
                'phone'       => 1,
                'call_status' => Call::REACHED,
                'note_id'     => null,
                'body'        => $body,
                'patient_id'  => $patientId,
            ]
        );

        self::assertNull($resp->exception);

        /** @var Note $note */
        $note = Note::whereBody($body)->first();
        self::assertNotNull($note);
        self::assertTrue('complete' === $note->status);

        return $note->id;
    }

    /**
     * Tests feature flag.
     */
    private function disableFeatureForPatient()
    {
        AppConfig::remove('enable_patient_login_for_practice', $this->patient->program_id);
    }

    /**
     * Tests feature flag.
     */
    private function enableFeatureForPatient()
    {
        AppConfig::set('enable_patient_login_for_practice', $this->patient->program_id);
    }

    /**
     * @return mixed
     */
    private function featureIsEnabledForPatient()
    {
        $values = AppConfig::pull('enable_patient_login_for_practice', []);

        return in_array($this->patient->program_id, $values);
    }

    /**
     * Log in as provider and approve careplan.
     */
    private function providerApprovesCarePlan()
    {
        $this->actingAs($this->provider)->call('POST', route('patient.careplan.approve', [
            'patientId' => $this->patient->id,
        ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('patient.careplan.print', [
                'patientId'    => $this->patient->id,
                'clearSession' => false,
            ]));

        auth()->logout();

        $this->patient->load('carePlan');

        $this->assertEquals($this->patient->carePlan->status, CarePlan::PROVIDER_APPROVED);
    }

    private function rnApproveCarePlan()
    {
        $this->actingAs($this->nurse);

        $this->patient->carePlan->status = CarePlan::QA_APPROVED;
        $this->patient->carePlan->save();
        $this->patient->carePlan->fresh();

        $this->assertEquals(CarePlan::QA_APPROVED, $this->patient->carePlan->status);

        $this->call('POST', route('patient.careplan.approve', [
            'patientId' => $this->patient->id,
        ]), [
            //in case patient has both types of diabetes
            'confirm_diabetes_conditions' => 1,
        ])
            ->assertSessionHasNoErrors();

        //need also a successful clinical note in this session for cp to be approved
        $this->createNote($this->patient->id, 'rnApproveCarePlan'.rand());

        auth()->logout();

        $this->patient->load('carePlan');
        //assert careplan has been QA approved
        $this->assertEquals($this->patient->carePlan->status, CarePlan::RN_APPROVED);
    }

    /**
     * Add patient details so it can pass care plan approval.
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
}
