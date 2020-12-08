<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature\SelfEnrollment;

use App\Console\Commands\UpdateEnrolleeProvidersThatCreatedWrong;
use App\EnrollmentInvitationsBatch;
use App\Http\Controllers\Enrollment\SelfEnrollmentController;
use App\Jobs\LogSuccessfulLoginToDB;
use App\Jobs\ProcessSelfEnrolablesFromCollectionJob;
use App\LoginLogout;
use App\SelfEnrollment\Domain\UnreachablesFinalAction;
use App\SelfEnrollment\Jobs\SendInvitation;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Traits\PracticeHelpers;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Notification;
use PrepareDataForReEnrollmentTestSeeder;
use Queue;
use Tests\TestCase;

class UpdatedWrongImportedEnrolleeProvidersTest extends TestCase
{
    use PracticeHelpers;
    use UserHelpers;

    private \CircleLinkHealth\Customer\Entities\User $newProviderToImport;
    private \CircleLinkHealth\Customer\Entities\User $newProviderToImport2;
    private \CircleLinkHealth\Customer\Entities\Practice $practice;
    private $seeder;
    private \CircleLinkHealth\Customer\Entities\User $user1;
    private \CircleLinkHealth\Customer\Entities\User $user2;
    private \CircleLinkHealth\Customer\Entities\User $wrongProviderThatShouldBeReplaced;

    public function createDataCollectionTest(
        ?bool $assignCareTeamProvider = true,
        ?bool $sendNotification = true,
        ?bool $assignTheWrongProvider = true,
        ?bool $assignCcdas = false
    ) {
        $dataToUpdate                            = [];
        $this->practice                          = $this->setUpPractice();
        $this->wrongProviderThatShouldBeReplaced = $this->createUser($this->practice->id, 'provider');
        $this->newProviderToImport               = $this->createUser($this->practice->id, 'provider');
        $this->newProviderToImport2              = $this->createUser($this->practice->id, 'provider');

        $updated = $this->wrongProviderThatShouldBeReplaced->update([
            'email' => UpdateEnrolleeProvidersThatCreatedWrong::WRONG_PROVIDER_EMAIL,
        ]);

        self::assertTrue($updated);

        /** @var \CircleLinkHealth\Customer\Entities\User $provider */
        $provider = $assignTheWrongProvider ? $this->wrongProviderThatShouldBeReplaced : $this->createUser($this->practice->id, 'provider');
        if ($sendNotification) {
            $enrollee1 = $this->factory()->createEnrollee($this->practice, $provider);
            $enrollee2 = $this->factory()->createEnrollee($this->practice, $provider);
            $enrollee1->load('user');
            $enrollee2->load('user');
            $this->user1 = $enrollee1->user;
            $this->user2 = $enrollee2->user;
            $patients    = [
                $this->user1,
                $this->user2,
            ];

            $color = SelfEnrollmentController::DEFAULT_BUTTON_COLOR;
            foreach ($patients as $patient) {
                $this->sendInvitationsAndTest($patient, $color);
            }
        } else {
            $this->user1 = $this->createUserWithEnrollee($provider->id);
            $this->user2 = $this->createUserWithEnrollee($provider->id);
        }

        $dataToUpdate[$this->user1->enrollee->id] = $this->newProviderToImport->display_name;
        $dataToUpdate[$this->user2->enrollee->id] = $this->newProviderToImport2->display_name;

        if ($assignCareTeamProvider) {
            $this->user1->careTeamMembers()->create([
                'member_user_id' => $this->wrongProviderThatShouldBeReplaced->id,
                'type'           => CarePerson::BILLING_PROVIDER,
            ]);

            $this->user2->careTeamMembers()->create([
                'member_user_id' => $this->wrongProviderThatShouldBeReplaced->id,
                'type'           => CarePerson::BILLING_PROVIDER,
            ]);
        }

        if ($assignCcdas) {
            $this->createCcda($this->user1->id, $this->wrongProviderThatShouldBeReplaced->id);
        }

        $this->user1->load('enrollee', 'careTeamMembers', 'enrollmentInvitationLinks', 'ccdas');
        $this->user2->load('enrollee', 'careTeamMembers', 'enrollmentInvitationLinks', 'ccdas');
        $this->user1->fresh();
        $this->user2->fresh();

        return $this->dataGroupedByProviderTesting($dataToUpdate);
    }

    public function test_it_will_mark_as_unresonsive_if_letter_not_seen()
    {
        $dataToUpdate = $this->createDataCollectionTest(false);
        $enrollee     = $this->user1->enrollee;
        ProcessSelfEnrolablesFromCollectionJob::dispatchNow($dataToUpdate, $this->practice->id);
        $this->assertDatabaseHas('enrollees', [
            'id'                        => $enrollee->id,
            'status'                    => Enrollee::TO_CALL,
            'auto_enrollment_triggered' => true,
            'enrollment_non_responsive' => true,
            'requested_callback'        => Carbon::parse(now()->addDays(UnreachablesFinalAction::TO_CALL_AFTER_DAYS_HAVE_PASSED))->toDateString(),
        ]);
    }

    public function test_it_will_not_process_data_if_care_team_provider_is_not_dan_becker()
    {
        $dataToUpdate = $this->createDataCollectionTest(false, true, false);
        ProcessSelfEnrolablesFromCollectionJob::dispatchNow($dataToUpdate, $this->practice->id);

        $providerId = $this->newProviderToImport->id;
        $userId     = $this->user1->id;

        $this->assertDatabaseHas('enrollees', [
            'user_id'     => $userId,
            'provider_id' => $this->user1->enrollee->provider_id,
            'status'      => Enrollee::QUEUE_AUTO_ENROLLMENT,
        ]);

        $this->assertDatabaseMissing('enrollees', [
            'user_id'     => $userId,
            'provider_id' => $providerId,
            'status'      => Enrollee::TO_CALL,
        ]);
    }

    public function test_it_will_not_process_data_if_enrolee_has_not_any_invitation_links()
    {
        $dataToUpdate = $this->createDataCollectionTest(true, false);
        $this->assertDataPreUpdate();

        ProcessSelfEnrolablesFromCollectionJob::dispatchNow($dataToUpdate, $this->practice->id);

        $providerId = $this->newProviderToImport->id;
        $userId     = $this->user1->id;

        $this->assertDatabaseHas('log', [
            'message' => "Enrollee with user_id [$userId] does not have an invitation link.",
        ]);

        $this->assertDatabaseHas('enrollees', [
            'user_id'     => $userId,
            'provider_id' => $this->wrongProviderThatShouldBeReplaced->id,
            'status'      => Enrollee::QUEUE_AUTO_ENROLLMENT,
        ]);

        $this->assertDatabaseMissing('enrollees', [
            'user_id'     => $userId,
            'provider_id' => $providerId,
            'status'      => Enrollee::TO_CALL,
        ]);
    }

    public function test_it_will_process_if_wrong_billing_provider_exists_in_ccda()
    {
        $dataToUpdate = $this->createDataCollectionTest(false, true, true, true);

        $this->assertCcdasFor($this->user1->id, $this->user1->enrollee->provider_id);
        $this->dispatchProcessorJob($dataToUpdate);

        $this->user1->fresh();
        $this->assertCcdasFor($this->user1->id, $this->newProviderToImport->id);
        $this->assertDatabaseHas('enrollees', [
            'user_id'     => $this->user1->id,
            'provider_id' => $this->newProviderToImport->id,
            'status'      => Enrollee::TO_CALL,
        ]);

        $this->assertDatabaseHas('patient_care_team_members', [
            'user_id'        => $this->user1->id,
            'member_user_id' => $this->newProviderToImport->id,
            'type'           => ProcessSelfEnrolablesFromCollectionJob::PROVIDER_TYPE,
        ]);
    }

    public function test_it_will_put_into_call_queue_if_letter_is_seen()
    {
        $dataToUpdate = $this->createDataCollectionTest(false);
        $enrollee     = $this->user1->enrollee;
        Queue::fake();
        Auth::loginUsingId($enrollee->user_id);

        Queue::assertPushed(LogSuccessfulLoginToDB::class, function (LogSuccessfulLoginToDB $job) use ($enrollee) {
            $job->handle();

            return LoginLogout::whereUserId($enrollee->user_id)->exists();
        });

        ProcessSelfEnrolablesFromCollectionJob::dispatchNow($dataToUpdate, $this->practice->id);
        $this->assertDatabaseHas('enrollees', [
            'id'                        => $enrollee->id,
            'status'                    => Enrollee::TO_CALL,
            'auto_enrollment_triggered' => true,
            'requested_callback'        => Carbon::parse(now()->addDays(UnreachablesFinalAction::TO_CALL_AFTER_DAYS_HAVE_PASSED))->toDateString(),
        ]);
    }

    public function test_it_will_update_enrollee_providers_using_data_collection()
    {
        $dataToUpdate = $this->createDataCollectionTest(false);

        $this->assertDataPreUpdate();
        $this->dispatchProcessorJob($dataToUpdate);
        $this->assertDataPostUpdate();
    }

    private function assertCcdasFor(int $patientId, ?int $providerId)
    {
        $this->assertDatabaseHas('ccdas', [
            'patient_id'          => $patientId,
            'billing_provider_id' => $providerId,
        ]);
    }

    private function assertDataPostUpdate()
    {
        $this->assertDatabaseHas('enrollees', [
            'user_id'     => $this->user1->id,
            'provider_id' => $this->newProviderToImport->id,
            'status'      => Enrollee::TO_CALL,
        ]);

        $this->assertDatabaseHas('enrollees', [
            'user_id'     => $this->user2->id,
            'provider_id' => $this->newProviderToImport2->id,
            'status'      => Enrollee::TO_CALL,
        ]);

        $this->assertDatabaseHas('patient_care_team_members', [
            'user_id'        => $this->user1->id,
            'member_user_id' => $this->newProviderToImport->id,
            'type'           => ProcessSelfEnrolablesFromCollectionJob::PROVIDER_TYPE,
        ]);

        $this->assertDatabaseHas('patient_care_team_members', [
            'user_id'        => $this->user2->id,
            'member_user_id' => $this->newProviderToImport2->id,
            'type'           => ProcessSelfEnrolablesFromCollectionJob::PROVIDER_TYPE,
        ]);
    }

    private function assertDataPreUpdate()
    {
        $this->assertDatabaseHas('enrollees', [
            'user_id'     => $this->user2->id,
            'provider_id' => $this->wrongProviderThatShouldBeReplaced->id,
            'status'      => Enrollee::QUEUE_AUTO_ENROLLMENT,
        ]);

        $this->assertDatabaseHas('patient_care_team_members', [
            'user_id'        => $this->user2->id,
            'member_user_id' => $this->wrongProviderThatShouldBeReplaced->id,
            'type'           => ProcessSelfEnrolablesFromCollectionJob::PROVIDER_TYPE,
        ]);
    }

    private function createCcda(int $userId, int $providerId)
    {
        Ccda::create([
            'patient_id'          => $userId,
            'billing_provider_id' => $providerId,
        ]);
    }

    /**
     * @return \CircleLinkHealth\Customer\Entities\User
     */
    private function createUserWithEnrollee(int $providerId)
    {
        $user = $this->createUser($this->practice->id, 'participant', Patient::PAUSED);
        $user->enrollee()->create([
            'provider_id' => $providerId,
            'status'      => Enrollee::QUEUE_AUTO_ENROLLMENT,
        ]);

        return $user;
    }

    private function dataGroupedByProviderTesting(array $dataToUpdateTesting)
    {
        return collect($dataToUpdateTesting)->mapToGroups(function ($providerName, $enrolleeId) {
            return [
                $providerName => $enrolleeId,
            ];
        });
    }

    private function factory()
    {
        if (is_null($this->seeder)) {
            $this->seeder = $this->app->make(PrepareDataForReEnrollmentTestSeeder::class);
        }

        return $this->seeder;
    }

    private function sendInvitationsAndTest(User $patient, string $color)
    {
        \Illuminate\Support\Facades\Queue::fake();
        SendInvitation::dispatch($patient, EnrollmentInvitationsBatch::firstOrCreateAndRemember(
            $patient->program_id,
            now()->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.EnrollmentInvitationsBatch::MANUAL_INVITES_BATCH_TYPE
        )->id, $color, false, ['mail']);

        \Illuminate\Support\Facades\Queue::assertPushed(SendInvitation::class, function (SendInvitation $job) use ($color) {
            Notification::fake();
            $job->handle();
            $this->assertDatabaseHas('enrollables_invitation_links', [
                'url'              => $job->getLink(),
                'manually_expired' => false,
                'button_color'     => $color,
            ]);

            return true;
        });
    }

    private function setUpPractice()
    {
        return Practice::firstOrCreate(
            [
                'name' => UpdateEnrolleeProvidersThatCreatedWrong::MARILLAC_NAME,
            ],
            [
                'active'                => 1,
                'display_name'          => 'Marillac',
                'is_demo'               => 1,
                'clh_pppm'              => 0,
                'term_days'             => 30,
                'outgoing_phone_number' => +16419544560,
            ]
        );
    }
    
    private function dispatchProcessorJob(Collection $dataToUpdate)
    {
        $dataToUpdate->each(function ($enrolleeIds, $providerName) {
            foreach ($enrolleeIds->chunk(100) as $chunk) {
                ProcessSelfEnrolablesFromCollectionJob::dispatchNow($chunk, $this->practice->id, $providerName);
            }
        });
    }
}
