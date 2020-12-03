<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature\SelfEnrollment;

use App\Console\Commands\UpdateEnrolleeProvidersThatCreatedWrong;
use App\Jobs\LogSuccessfulLoginToDB;
use App\Jobs\UpdateEnrolleeFromCollectionJob;
use App\LoginLogout;
use App\SelfEnrollment\Domain\UnreachablesFinalAction;
use App\ValueObjects\SelfEnrolment\MarillacEnrolleeProvidersValueObject;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Traits\PracticeHelpers;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Support\Facades\Auth;
use Queue;
use Tests\TestCase;

class UpdatedWrongImportedEnrolleeProvidersTest extends TestCase
{
    use PracticeHelpers;
    use UserHelpers;

    private \CircleLinkHealth\Customer\Entities\User $newProviderToImport;
    private \CircleLinkHealth\Customer\Entities\User $newProviderToImport2;
    private \CircleLinkHealth\Customer\Entities\Practice $practice;
    private \CircleLinkHealth\Customer\Entities\User $provider;
    private \CircleLinkHealth\Customer\Entities\User $user1;
    private \CircleLinkHealth\Customer\Entities\User $user2;

    public function createDataCollectionTest(?bool $assignCareTeamProvider = true)
    {
        $dataToUpdate               = [];
        $this->practice             = $this->setUpMarillacPractice();
        $this->provider             = $this->createUser($this->practice->id, 'provider');
        $this->newProviderToImport  = $this->createUser($this->practice->id, 'provider');
        $this->newProviderToImport2 = $this->createUser($this->practice->id, 'provider');

        $this->user1                              = $this->createUserWithEnrollee($this->provider->id);
        $dataToUpdate[$this->user1->enrollee->id] = $this->newProviderToImport->display_name;

        $this->user2                              = $this->createUserWithEnrollee($this->provider->id);
        $dataToUpdate[$this->user2->enrollee->id] = $this->newProviderToImport2->display_name;

        if ($assignCareTeamProvider) {
            $this->user1->careTeamMembers()->create([
                'member_user_id' => $this->newProviderToImport->id,
                'type'           => CarePerson::BILLING_PROVIDER,
            ]);

            $this->user2->careTeamMembers()->create([
                'member_user_id' => $this->newProviderToImport2->id,
                'type'           => CarePerson::BILLING_PROVIDER,
            ]);
        }

        $this->user1->load('enrollee', 'careTeamMembers');
        $this->user2->load('enrollee', 'careTeamMembers');
        $this->user1->fresh();
        $this->user2->fresh();

        return (new MarillacEnrolleeProvidersValueObject())->dataGroupedByProviderTesting($dataToUpdate);
    }

    public function test_it_will_not_process_data_if_provider_to_replace_does_not_match_care_team_member_provider_id()
    {
        $dataToUpdate = $this->createDataCollectionTest(false);
        $this->assertDataPreUpdate();
        UpdateEnrolleeFromCollectionJob::dispatchNow($dataToUpdate, $this->practice->id);

        $correctProviderId1 = $this->newProviderToImport->id;
        $correctProviderId2 = $this->newProviderToImport2->id;
        $this->assertDatabaseHas('log', [
            'message' => "Provider id to update [$correctProviderId1] does not match careTeamMembers [member_user_id]",
        ]);

        $this->assertDatabaseHas('enrollees', [
            'user_id'     => $this->user1->id,
            'provider_id' => $this->provider->id,
            'status'      => Enrollee::QUEUE_AUTO_ENROLLMENT,
        ]);

        $this->assertDatabaseHas('enrollees', [
            'user_id'     => $this->user2->id,
            'provider_id' => $this->provider->id,
            'status'      => Enrollee::QUEUE_AUTO_ENROLLMENT,
        ]);

        $this->assertDatabaseMissing('enrollees', [
            'user_id'     => $this->user1->id,
            'provider_id' => $correctProviderId1,
            'status'      => Enrollee::QUEUE_AUTO_ENROLLMENT,
        ]);

        $this->assertDatabaseMissing('enrollees', [
            'user_id'     => $this->user2->id,
            'provider_id' => $correctProviderId2,
            'status'      => Enrollee::QUEUE_AUTO_ENROLLMENT,
        ]);
    }

    public function test_it_will_put_into_call_queue_if_letter_is_seen_or_mark_as_unresponsive_if_not()
    {
        $dataToUpdate = $this->createDataCollectionTest();
        $enrollee     = $this->user1->enrollee;
        $enrollee2    = $this->user2->enrollee;
        Queue::fake();
        Auth::loginUsingId($enrollee->user_id);

        Queue::assertPushed(LogSuccessfulLoginToDB::class, function (LogSuccessfulLoginToDB $job) use ($enrollee) {
            $job->handle();

            return LoginLogout::whereUserId($enrollee->user_id)->exists();
        });

        UpdateEnrolleeFromCollectionJob::dispatchNow($dataToUpdate, $this->practice->id);
        $this->assertDatabaseHas('enrollees', [
            'id'                        => $enrollee->id,
            'status'                    => Enrollee::TO_CALL,
            'auto_enrollment_triggered' => true,
            'requested_callback'        => Carbon::parse(now()->addDays(UnreachablesFinalAction::TO_CALL_AFTER_DAYS_HAVE_PASSED))->toDateString(),
        ]);

        $this->assertDatabaseHas('enrollees', [
            'id'                        => $enrollee2->id,
            'status'                    => Enrollee::TO_CALL,
            'auto_enrollment_triggered' => true,
            'enrollment_non_responsive' => true,
            'requested_callback'        => Carbon::parse(now()->addDays(UnreachablesFinalAction::TO_CALL_AFTER_DAYS_HAVE_PASSED))->toDateString(),
        ]);
    }

    public function test_it_will_update_enrollee_providers_using_data_collection()
    {
        $dataToUpdate = $this->createDataCollectionTest();

        $this->assertDataPreUpdate();

        UpdateEnrolleeFromCollectionJob::dispatchNow($dataToUpdate, $this->practice->id);

        $this->assertDataPostUpdate();
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
    }

    private function assertDataPreUpdate()
    {
        $this->assertDatabaseHas('enrollees', [
            'user_id'     => $this->user2->id,
            'provider_id' => $this->provider->id,
            'status'      => Enrollee::QUEUE_AUTO_ENROLLMENT,
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

    private function setUpMarillacPractice()
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
}
