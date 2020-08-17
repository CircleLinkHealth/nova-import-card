<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Algorithms\Calls\NurseFinder\NurseFinderEloquentRepository;
use App\AppConfig\DMDomainForAutoApproval;
use App\Call;
use App\DirectMailMessage;
use App\Events\CarePlanWasApproved;
use App\Listeners\ChangeOrApproveCareplanResponseListener;
use App\Note;
use App\Notifications\CarePlanDMApprovalConfirmation;
use App\Notifications\Channels\DirectMailChannel;
use App\Notifications\SendCarePlanForDirectMailApprovalNotification;
use App\Services\Calls\SchedulerService;
use App\Services\PhiMail\Events\DirectMailMessageReceived;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Core\Facades\Notification;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Support\Str;
use Tests\CustomerTestCase;

class ApproveCPViaDM extends CustomerTestCase
{
    const TEST_DM_DOMAIN = '@test.directproject.net';

    public function directMailSubject($patient): string
    {
        return "{$patient->getFullName()}'s CCM Care Plan to approve!";
    }

    public function test_care_plan_is_approved_when_task_is_resolved()
    {
        $this->patient()->setCarePlanStatus(CarePlan::QA_APPROVED);
        $this->assertEquals(CarePlan::QA_APPROVED, $this->patient()->carePlan->status);

        app(NurseFinderEloquentRepository::class)->assign(
            $this->patient()->id,
            $this->careCoach()->id
        );

        $task         = $this->fakeTask();
        $task->status = 'done';
        $task->save();

        $this->assertTrue($this->patient()->carePlan->wasApprovedViaNurse());
        $this->assertEquals($this->careCoach()->getFullName(), $this->patient()->carePlan->getNurseApproverName());

        $this->assertEquals(
            CarePlan::PROVIDER_APPROVED,
            $this->patient()->carePlan()->firstOrFail()->status,
            'Careplan was not approved after DM with approval code was received.'
        );
    }

    public function test_careplan_dm_approval_notification_channels()
    {
        $this->assertEquals(
            ['database', DirectMailChannel::class],
            (new SendCarePlanForDirectMailApprovalNotification($this->patient()))->via(
                $this->provider()
            )
        );
    }

    public function test_careplan_dm_approved_confirmation_notification_channels()
    {
        $this->assertEquals(
            ['database', DirectMailChannel::class],
            (new CarePlanDMApprovalConfirmation($this->patient()))->via($this->provider())
        );
    }

    public function test_cp_approve_notification_is_sent_via_dm()
    {
        $this->patient()->carePlan->status = CarePlan::QA_APPROVED;
        $this->patient()->carePlan->save();

        $this->provider()->emr_direct_address = 'circlelinkhealth'.self::TEST_DM_DOMAIN;

        $notification = new SendCarePlanForDirectMailApprovalNotification($this->patient());
        $this->provider()->notify($notification);

        $this->assertDatabaseHas(
            'passwordless_login_tokens',
            [
                'user_id' => $this->provider()->id,
                'token'   => $notification->token($this->provider())->token,
            ]
        );

        $this->assertDatabaseHas(
            (new DirectMailMessage())->getTable(),
            [
                'from'       => config('services.emr-direct.user'),
                'to'         => 'circlelinkhealth'.self::TEST_DM_DOMAIN,
                'subject'    => $this->directMailSubject($this->patient()),
                'status'     => DirectMailMessage::STATUS_SUCCESS,
                'direction'  => DirectMailMessage::DIRECTION_SENT,
                'error_text' => null,
            ]
        );
    }

    public function test_extracting_approval_or_rejection_codes()
    {
        $listener = app(ChangeOrApproveCareplanResponseListener::class);

        $this->assertEquals(120, $listener->getCareplanIdToApprove('   #approve120'));
        $this->assertEquals(120, $listener->getCareplanIdToApprove('#approve120'));
        $this->assertEquals(32, $listener->getCareplanIdToApprove('#approve 32'));
        $this->assertEquals(3, $listener->getCareplanIdToApprove('#approve       3   '));
        $this->assertEquals(3, $listener->getCareplanIdToApprove('#      approve       3   '));

        $this->assertEquals(2, $listener->getCareplanIdToChange('#change2'));
        $this->assertEquals(312, $listener->getCareplanIdToChange('# change 312'));
    }

    public function test_it_does_not_create_note_if_note_is_direction_sent()
    {
        $this->enableFeatureFlag($this->practice()->id);

        $patient = $this->patient();
        $patient->setCarePlanStatus(CarePlan::QA_APPROVED);
        $this->assertEquals(CarePlan::QA_APPROVED, $patient->carePlan->status);
        $patient->setBillingProviderId($this->provider()->id);

        $this->provider()->emr_direct_address = 'drtest'.self::TEST_DM_DOMAIN.Str::random(5);

        $changeCode = "#change{$patient->carePlan->id}";
        $body       = "Please make the following changes for this patient $changeCode";
        $directMail = factory(DirectMailMessage::class)->create(
            [
                'direction' => DirectMailMessage::DIRECTION_SENT,
                'body'      => $body,
                'from'      => $this->provider()->emr_direct_address,
            ]
        );

        event(new DirectMailMessageReceived($directMail));

        $this->assertDatabaseMissing(
            'notes',
            [
                'patient_id' => $patient->id,
                'author_id'  => $this->provider()->id,
                'type'       => SchedulerService::PROVIDER_REQUEST_FOR_CAREPLAN_APPROVAL_TYPE,
                'body'       => $body,
            ]
        );
    }

    public function test_it_sends_careplan_approval_dm_upon_qa_approval()
    {
        $this->enableFeatureFlag($this->practice()->id);

        Notification::fake();
        $this->actingAs($this->superadmin());

        $patient = $this->patient();
        $patient->setCarePlanStatus(CarePlan::DRAFT);
        $patient->setBillingProviderId($this->provider()->id);

        $this->assertEquals(CarePlan::DRAFT, $patient->carePlan->status);
        event(new CarePlanWasApproved($patient, $this->superadmin()));
        $patient->carePlan->fresh();
        $this->assertEquals(CarePlan::QA_APPROVED, $patient->carePlan->status);

        Notification::assertSentTo(
            $this->provider(),
            SendCarePlanForDirectMailApprovalNotification::class,
            function (SendCarePlanForDirectMailApprovalNotification $notification, $channels, $notifiable) use ($patient
            ) {
                $this->assertContains(DirectMailChannel::class, $channels);
                $this->assertStringContainsString(
                    '#approve'.$patient->carePlan->id,
                    $notification->directMailBody($notifiable)
                );
                $this->assertStringContainsString(
                    '#change'.$patient->carePlan->id,
                    $notification->directMailBody($notifiable)
                );
                $this->assertDatabaseHas(
                    'passwordless_login_tokens',
                    [
                        'user_id' => $this->provider()->id,
                        'token'   => $notification->token($this->provider())->token,
                    ]
                );

                return true;
            }
        );
    }

    public function test_provider_can_approve_careplan_with_valid_dm_response()
    {
        $this->enableFeatureFlag($this->practice()->id);

        Notification::fake();
        $patient = $this->patient();
        $patient->setCarePlanStatus(CarePlan::QA_APPROVED);
        $this->assertEquals(CarePlan::QA_APPROVED, $patient->carePlan->status);
        $patient->setBillingProviderId($this->provider()->id);

        $this->provider()->emr_direct_address = 'drtest'.self::TEST_DM_DOMAIN.Str::random(5);

        $approvalCode = "#approve{$patient->carePlan->id}";
        $directMail   = factory(DirectMailMessage::class)->create(
            [
                'body' => "Yes, I approve $approvalCode",
                'from' => $this->provider()->emr_direct_address,
            ]
        );

        event(new DirectMailMessageReceived($directMail));

        $this->assertEquals(
            CarePlan::PROVIDER_APPROVED,
            $patient->carePlan->fresh()->status,
            'Careplan was not approved after DM with approval code was received.'
        );

        Notification::assertSentTo(
            $this->provider(),
            CarePlanDMApprovalConfirmation::class,
            function (CarePlanDMApprovalConfirmation $notification, $channels, $notifiable) use ($patient) {
                $this->assertEquals('Care Plan Approved', $notification->directMailSubject($notifiable));
                $this->assertEquals(
                    "Thanks for approving {$patient->getFullName()}}'s Care Plan! Have a great day - CircleLink Team",
                    $notification->directMailBody($notifiable)
                );

                return true;
            }
        );
    }

    public function test_provider_can_create_task_with_valid_dm_response()
    {
        $this->enableFeatureFlag($this->practice()->id);

        $patient = $this->patient();
        $patient->setCarePlanStatus(CarePlan::QA_APPROVED);
        $this->assertEquals(CarePlan::QA_APPROVED, $patient->carePlan->status);
        $patient->setBillingProviderId($this->provider()->id);

        $this->provider()->emr_direct_address = 'drtest'.self::TEST_DM_DOMAIN.Str::random(5);

        $changeCode = "#change{$patient->carePlan->id}";
        $taskBody   = "Please make the following changes for this patient $changeCode";
        $directMail = factory(DirectMailMessage::class)->create(
            [
                'body' => $taskBody,
                'from' => $this->provider()->emr_direct_address,
            ]
        );

        event(new DirectMailMessageReceived($directMail));

        $note = Note::where(
            [
                ['patient_id', '=', $patient->id],
                ['author_id', '=', $this->provider()->id],
                ['type', '=', SchedulerService::PROVIDER_REQUEST_FOR_CAREPLAN_APPROVAL_TYPE],
                ['body', '=', $taskBody],
            ]
        )->firstOrFail();

        $this->assertDatabaseHas(
            'calls',
            [
                'type'            => 'task',
                'sub_type'        => SchedulerService::PROVIDER_REQUEST_FOR_CAREPLAN_APPROVAL_TYPE,
                'service'         => 'phone',
                'status'          => 'scheduled',
                'attempt_note'    => $taskBody,
                'scheduler'       => $this->provider()->id,
                'inbound_cpm_id'  => $patient->id,
                'outbound_cpm_id' => $this->app->make(NurseFinderEloquentRepository::class)->find($patient->id),
                'asap'            => true,
                'note_id'         => $note->id,
            ]
        );
    }

    public function tests_provider_can_login_with_passwordless_link_and_redirects_to_patient_careplan()
    {
        $this->assertFalse(auth()->check());

        $notification = new SendCarePlanForDirectMailApprovalNotification($this->patient());

        $link = $notification->passwordlessLoginLink($this->provider());

        $response = $this->get($link)->assertStatus(302);

        $this->assertEquals($this->provider()->id, auth()->id());

        $response->assertHeader('location', route('patient.careplan.print', [$this->patient()->id]));

        $this->assertDatabaseMissing(
            'passwordless_login_tokens',
            [
                'user_id' => $this->provider()->id,
                'token'   => $notification->token($this->provider())->token,
            ]
        );
    }

    private function enableFeatureFlag(int $practiceId)
    {
        AppConfig::set(DMDomainForAutoApproval::FLAG_NAME, $practiceId.self::TEST_DM_DOMAIN);
    }

    private function fakeTask()
    {
        $note = Note::create(
            [
                'patient_id' => $this->patient()->id,
                'author_id'  => $this->provider()->id,
                'type'       => SchedulerService::PROVIDER_REQUEST_FOR_CAREPLAN_APPROVAL_TYPE,
                'body'       => 'Instructions from provider',
            ]
        );

        return Call::create(
            [
                'type'            => 'task',
                'sub_type'        => SchedulerService::PROVIDER_REQUEST_FOR_CAREPLAN_APPROVAL_TYPE,
                'service'         => 'phone',
                'status'          => 'scheduled',
                'attempt_note'    => 'This is a task',
                'scheduler'       => $this->provider()->id,
                'inbound_cpm_id'  => $this->patient()->id,
                'outbound_cpm_id' => $this->app->make(NurseFinderEloquentRepository::class)->find($this->patient()->id),
                'asap'            => true,
                'note_id'         => $note->id,
            ]
        );
    }
}
