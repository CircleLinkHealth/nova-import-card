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

    private \CircleLinkHealth\Customer\Entities\User $newProvider;
    private \CircleLinkHealth\Customer\Entities\User $newProvider2;
    private \CircleLinkHealth\Customer\Entities\Practice $practice;
    private \CircleLinkHealth\Customer\Entities\User $provider;
    private \CircleLinkHealth\Customer\Entities\User $user1;
    private \CircleLinkHealth\Customer\Entities\User $user2;

    public function createDataCollectionTest()
    {
        $dataToUpdate       = [];
        $this->practice     = $this->setUpMarillacPractice();
        $this->provider     = $this->createUser($this->practice->id, 'provider');
        $this->newProvider  = $this->createUser($this->practice->id, 'provider');
        $this->newProvider2 = $this->createUser($this->practice->id, 'provider');

        $this->user1                              = $this->createUserWithEnrollee($this->provider->id);
        $dataToUpdate[$this->user1->enrollee->id] = $this->newProvider->display_name;
        $this->user2                              = $this->createUserWithEnrollee($this->provider->id);
        $dataToUpdate[$this->user2->enrollee->id] = $this->newProvider2->display_name;

        return (new MarillacEnrolleeProvidersValueObject())->dataGroupedByProviderTesting($dataToUpdate);
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
            'provider_id' => $this->newProvider->id,
            'status'      => Enrollee::QUEUE_AUTO_ENROLLMENT,
        ]);

        $this->assertDatabaseHas('enrollees', [
            'provider_id' => $this->newProvider2->id,
            'status'      => Enrollee::QUEUE_AUTO_ENROLLMENT,
        ]);
    }

    private function assertDataPreUpdate()
    {
        $this->assertDatabaseHas('enrollees', [
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
        $user->load('enrollee');
        $user->fresh();

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
